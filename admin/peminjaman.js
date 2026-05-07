/**
 * peminjaman.js — Manajemen Peminjaman Admin Panel
 */
(function () {
    'use strict';

    const API = 'api_peminjaman.php';
    let currentPage = 1;
    let searchTimer = null;
    let rejectId = null;
    let approveId = null;
    let barangCache = [];

    const $ = (s) => document.querySelector(s);
    const tableBody      = $('#tableBody');
    const pagination     = $('#pagination');
    const tableInfo      = $('#tableInfo');
    const searchInput    = $('#searchInput');
    const filterStatus   = $('#filterStatus');
    const toastContainer = $('#toastContainer');

    // Modals
    const modalDetail  = $('#modalDetail');
    const modalReject  = $('#modalReject');
    const modalCreate  = $('#modalCreate');
    const modalApprove = $('#modalApprove');

    // ===== Init =====
    loadStats();
    loadData();

    // ===== Events =====
    $('#btnRefresh').addEventListener('click', () => { loadStats(); loadData(); });
    $('#btnCreatePeminjaman').addEventListener('click', openCreateModal);
    $('#btnConfirmReject').addEventListener('click', confirmReject);
    $('#btnConfirmApprove').addEventListener('click', confirmApprove);
    $('#btnAddItem').addEventListener('click', addItemRow);
    $('#formCreate').addEventListener('submit', (e) => { e.preventDefault(); submitCreate(); });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { currentPage = 1; loadData(); }, 350);
    });
    filterStatus.addEventListener('change', () => { currentPage = 1; loadData(); });

    // Close modals on overlay click
    [modalDetail, modalReject, modalCreate, modalApprove].forEach(m => {
        m.addEventListener('click', (e) => { if (e.target === m) m.classList.remove('active'); });
    });

    // Sidebar toggle
    const btnToggle = $('#btnToggleSidebar');
    const sidebar = $('#sidebar');
    const overlay = $('#sidebarOverlay');
    if (btnToggle) {
        btnToggle.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
        overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
    }

    // ===== API =====
    async function apiFetch(action, params = {}) {
        const url = new URL(API, location.href);
        url.searchParams.set('action', action);
        Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
        return (await fetch(url)).json();
    }
    async function apiPost(action, body) {
        const url = new URL(API, location.href);
        url.searchParams.set('action', action);
        return (await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })).json();
    }

    // ===== Stats =====
    async function loadStats() {
        try {
            const d = await apiFetch('stats');
            if (d.success) {
                anim($('#statTotal'), d.total);
                anim($('#statPending'), d.pending);
                anim($('#statApproved'), d.approved);
                anim($('#statTerlambat'), d.terlambat);
            }
        } catch (e) { console.error(e); }
    }
    function anim(el, target) {
        const dur = 600, start = parseInt(el.textContent) || 0, diff = target - start;
        if (!diff) { el.textContent = target; return; }
        const t0 = performance.now();
        (function step(now) {
            const p = Math.min((now - t0) / dur, 1);
            el.textContent = Math.round(start + diff * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(step);
        })(performance.now());
    }

    // ===== Load Table =====
    async function loadData() {
        tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--outline)"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data…</td></tr>';
        try {
            const params = { page: currentPage };
            const s = searchInput.value.trim();
            const st = filterStatus.value;
            if (s) params.search = s;
            if (st) params.status = st;

            const d = await apiFetch('list', params);
            if (!d.success) throw new Error(d.message);

            if (d.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-clipboard"></i><h3>Tidak ada data peminjaman</h3><p>Belum ada pengajuan peminjaman.</p></div></td></tr>';
                tableInfo.textContent = '0 peminjaman';
                pagination.innerHTML = '';
                return;
            }

            tableBody.innerHTML = d.data.map((r, i) => {
                const ini = getInitials(r.nama_lengkap);
                const tgl = formatDate(r.tgl_pengajuan);
                const statusHTML = statusBadge(r.status_approval);
                const barangTruncated = r.daftar_barang
                    ? (r.daftar_barang.length > 40 ? r.daftar_barang.substring(0, 40) + '…' : r.daftar_barang)
                    : '—';
                return `
                <tr style="animation:fadeIn .3s ease ${i * .04}s both">
                    <td><span class="cell-id">${esc(r.id_peminjaman)}</span></td>
                    <td>
                        <div class="peminjam-cell">
                            <div class="peminjam-avatar">${ini}</div>
                            <div>
                                <div class="peminjam-name">${esc(r.nama_lengkap)}</div>
                                <div class="peminjam-username">@${esc(r.username)}</div>
                            </div>
                        </div>
                    </td>
                    <td>${tgl}</td>
                    <td style="text-align:center">${r.jumlah_item}</td>
                    <td><span class="barang-list-cell" title="${esc(r.daftar_barang || '')}">${esc(barangTruncated)}</span></td>
                    <td>${statusHTML}</td>
                    <td>
                        <div class="actions-cell" style="justify-content:flex-end">
                            <button class="btn btn-ghost btn-sm btn-icon" title="Detail" onclick="APP.detail('${esc(r.id_peminjaman)}')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            ${r.status_approval === 'pending' ? `
                            <button class="btn btn-ghost btn-sm btn-icon" title="Setujui" onclick="APP.openApprove('${esc(r.id_peminjaman)}')" style="color:#059669">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm btn-icon" title="Tolak" onclick="APP.openReject('${esc(r.id_peminjaman)}')" style="color:var(--error)">
                                <i class="fa-solid fa-xmark"></i>
                            </button>` : ''}
                        </div>
                    </td>
                </tr>`;
            }).join('');

            const from = (d.page - 1) * 10 + 1;
            const to = Math.min(d.page * 10, d.total);
            tableInfo.textContent = `Menampilkan ${from}–${to} dari ${d.total} peminjaman`;
            renderPagination(d.page, d.pages);
        } catch (e) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--error)"><i class="fa-solid fa-circle-exclamation"></i> ${e.message}</td></tr>`;
        }
    }

    function statusBadge(s) {
        const map = {
            pending:    { cls: 'badge-amber', icon: 'fa-hourglass-half', text: 'Pending' },
            disetujui:  { cls: 'badge-green', icon: 'fa-circle-check',   text: 'Disetujui' },
            ditolak:    { cls: 'badge-red',   icon: 'fa-circle-xmark',   text: 'Ditolak' },
        };
        const m = map[s] || map.pending;
        return `<span class="badge ${m.cls}"><i class="fa-solid ${m.icon}" style="font-size:.55rem"></i> ${m.text}</span>`;
    }

    function renderPagination(cur, total) {
        if (total <= 1) { pagination.innerHTML = ''; return; }
        let h = `<button class="page-btn" ${cur <= 1 ? 'disabled' : ''} onclick="APP.page(${cur - 1})"><i class="fa-solid fa-chevron-left" style="font-size:.65rem"></i></button>`;
        for (let i = 1; i <= total; i++) {
            if (total > 7 && i > 2 && i < total - 1 && Math.abs(i - cur) > 1) {
                if (i === 3 || i === total - 2) h += '<span style="padding:0 .25rem;color:var(--outline)">…</span>';
                continue;
            }
            h += `<button class="page-btn ${i === cur ? 'active' : ''}" onclick="APP.page(${i})">${i}</button>`;
        }
        h += `<button class="page-btn" ${cur >= total ? 'disabled' : ''} onclick="APP.page(${cur + 1})"><i class="fa-solid fa-chevron-right" style="font-size:.65rem"></i></button>`;
        pagination.innerHTML = h;
    }

    // ===== Detail Modal =====
    async function showDetail(id) {
        modalDetail.classList.add('active');
        $('#detailContent').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--outline)"><i class="fa-solid fa-spinner fa-spin"></i> Memuat…</div>';
        $('#detailFooter').innerHTML = '';

        try {
            const d = await apiFetch('detail', { id });
            if (!d.success) throw new Error(d.message);

            const p = d.peminjaman;
            const items = d.items;
            const statusHTML = statusBadge(p.status_approval);

            let itemsRows = items.map(it => {
                const itemStatusBadge = itemBadge(it.status_item);
                return `<tr>
                    <td><span class="cell-id">${esc(it.id_barang)}</span></td>
                    <td class="cell-name">${esc(it.nama_barang)}</td>
                    <td style="text-align:center">${it.jumlah}</td>
                    <td>${it.tgl_pinjam || '—'}</td>
                    <td>${it.tgl_kembali_rencana || '—'}</td>
                    <td>${itemStatusBadge}</td>
                </tr>`;
            }).join('');

            let rejectionHTML = '';
            if (p.status_approval === 'ditolak' && p.alasan_tolak) {
                rejectionHTML = `<div class="rejection-box"><i class="fa-solid fa-circle-info"></i> ${esc(p.alasan_tolak)}</div>`;
            }

            $('#detailContent').innerHTML = `
                <div class="detail-info">
                    <div class="detail-field">
                        <span class="detail-label">ID Peminjaman</span>
                        <span class="detail-value" style="font-family:monospace">${esc(p.id_peminjaman)}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">${statusHTML}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Peminjam</span>
                        <span class="detail-value">${esc(p.nama_lengkap)} <span style="color:var(--outline);font-size:.78rem">(@${esc(p.username)})</span></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Tanggal Pengajuan</span>
                        <span class="detail-value">${formatDate(p.tgl_pengajuan)}</span>
                    </div>
                    ${p.admin_nama ? `<div class="detail-field">
                        <span class="detail-label">Diproses Oleh</span>
                        <span class="detail-value">${esc(p.admin_nama)}</span>
                    </div>` : ''}
                    ${p.no_hp ? `<div class="detail-field">
                        <span class="detail-label">No. HP</span>
                        <span class="detail-value">${esc(p.no_hp)}</span>
                    </div>` : ''}
                </div>
                ${rejectionHTML}
                <hr class="detail-divider">
                <div class="detail-section-title"><i class="fa-solid fa-boxes-stacked"></i> Daftar Barang Dipinjam</div>
                <div style="overflow-x:auto">
                    <table class="detail-items-table">
                        <thead><tr>
                            <th>Kode</th><th>Nama Barang</th><th>Jumlah</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th>
                        </tr></thead>
                        <tbody>${itemsRows}</tbody>
                    </table>
                </div>`;

            // Footer buttons
            if (p.status_approval === 'pending') {
                $('#detailFooter').innerHTML = `
                    <button class="btn btn-secondary" onclick="APP.closeDetail()">Tutup</button>
                    <button class="btn btn-danger" onclick="APP.closeDetail();APP.openReject('${esc(p.id_peminjaman)}')"><i class="fa-solid fa-xmark"></i> Tolak</button>
                    <button class="btn btn-primary" onclick="APP.closeDetail();APP.openApprove('${esc(p.id_peminjaman)}')"><i class="fa-solid fa-check"></i> Setujui</button>`;
            } else {
                $('#detailFooter').innerHTML = `<button class="btn btn-secondary" onclick="APP.closeDetail()">Tutup</button>`;
            }
        } catch (e) {
            $('#detailContent').innerHTML = `<div style="text-align:center;padding:2rem;color:var(--error)"><i class="fa-solid fa-circle-exclamation"></i> ${e.message}</div>`;
        }
    }

    function itemBadge(s) {
        const map = {
            dipinjam:      { cls: 'badge-blue',  text: 'Dipinjam' },
            dikembalikan:  { cls: 'badge-green', text: 'Dikembalikan' },
            terlambat:     { cls: 'badge-red',   text: 'Terlambat' },
            keluar:        { cls: 'badge-amber', text: 'Keluar' },
        };
        const m = map[s] || map.dipinjam;
        return `<span class="badge ${m.cls}">${m.text}</span>`;
    }

    // ===== Approve =====
    function openApproveModal(id) {
        approveId = id;
        modalApprove.classList.add('active');
    }
    async function confirmApprove() {
        if (!approveId) return;
        const btn = $('#btnConfirmApprove');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses…';
        try {
            const r = await apiPost('approve', { id_peminjaman: approveId });
            if (r.success) { showToast(r.message, 'success'); modalApprove.classList.remove('active'); loadStats(); loadData(); }
            else showToast(r.message, 'error');
        } catch (e) { showToast('Terjadi kesalahan', 'error'); }
        finally { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-check"></i> Setujui'; approveId = null; }
    }

    // ===== Reject =====
    function openRejectModal(id) {
        rejectId = id;
        $('#inpAlasanTolak').value = '';
        modalReject.classList.add('active');
        setTimeout(() => $('#inpAlasanTolak').focus(), 200);
    }
    async function confirmReject() {
        if (!rejectId) return;
        const btn = $('#btnConfirmReject');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses…';
        try {
            const r = await apiPost('reject', { id_peminjaman: rejectId, alasan: $('#inpAlasanTolak').value.trim() });
            if (r.success) { showToast(r.message, 'success'); modalReject.classList.remove('active'); loadStats(); loadData(); }
            else showToast(r.message, 'error');
        } catch (e) { showToast('Terjadi kesalahan', 'error'); }
        finally { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-xmark"></i> Tolak Peminjaman'; rejectId = null; }
    }

    // ===== Create Modal =====
    async function openCreateModal() {
        // Load users & barang
        try {
            const [usersRes, barangRes] = await Promise.all([apiFetch('users_list'), apiFetch('barang_list')]);
            if (usersRes.success) {
                const sel = $('#inpUser');
                sel.innerHTML = '<option value="">Pilih anggota…</option>' +
                    usersRes.data.map(u => `<option value="${u.id_user}">${esc(u.nama_lengkap)} (@${esc(u.username)})</option>`).join('');
            }
            if (barangRes.success) {
                barangCache = barangRes.data;
            }
        } catch (e) { console.error(e); }

        // Set defaults
        const today = new Date().toISOString().split('T')[0];
        $('#inpTglPinjam').value = today;
        const next = new Date(); next.setDate(next.getDate() + 7);
        $('#inpTglKembali').value = next.toISOString().split('T')[0];

        // Clear items
        $('#itemRows').innerHTML = '';
        addItemRow();

        // Clear errors
        document.querySelectorAll('.form-group').forEach(g => g.classList.remove('has-error'));

        modalCreate.classList.add('active');
    }

    function addItemRow() {
        const container = $('#itemRows');
        const row = document.createElement('div');
        row.className = 'item-row';

        const opts = barangCache.map(b =>
            `<option value="${b.id_barang}">${esc(b.nama_barang)} (stok: ${b.stok_tersedia})</option>`
        ).join('');

        row.innerHTML = `
            <div class="form-group">
                <select class="form-select item-barang">
                    <option value="">Pilih barang…</option>
                    ${opts}
                </select>
            </div>
            <div class="form-group">
                <input type="number" class="form-input item-jumlah" min="1" value="1" placeholder="Qty">
            </div>
            <button type="button" class="btn-remove-item" title="Hapus"><i class="fa-solid fa-xmark"></i></button>`;

        row.querySelector('.btn-remove-item').addEventListener('click', () => {
            if (container.children.length > 1) row.remove();
        });

        container.appendChild(row);
    }

    async function submitCreate() {
        // Clear errors
        document.querySelectorAll('.form-group').forEach(g => g.classList.remove('has-error'));
        let valid = true;

        const id_user = $('#inpUser').value;
        const tgl_pinjam = $('#inpTglPinjam').value;
        const tgl_kembali = $('#inpTglKembali').value;

        if (!id_user) { $('#grpUser').classList.add('has-error'); valid = false; }
        if (!tgl_pinjam) { $('#grpTglPinjam').classList.add('has-error'); valid = false; }
        if (!tgl_kembali) { $('#grpTglKembali').classList.add('has-error'); valid = false; }

        const itemRows = document.querySelectorAll('.item-row');
        const items = [];
        itemRows.forEach(row => {
            const id_barang = row.querySelector('.item-barang').value;
            const jumlah = parseInt(row.querySelector('.item-jumlah').value) || 0;
            if (id_barang && jumlah > 0) items.push({ id_barang, jumlah });
        });

        if (items.length === 0) {
            showToast('Tambahkan minimal 1 barang', 'error');
            valid = false;
        }

        if (!valid) return;

        const btn = $('#btnSubmitCreate');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan…';

        try {
            const r = await apiPost('create', { id_user, items, tgl_pinjam, tgl_kembali });
            if (r.success) {
                showToast(r.message, 'success');
                modalCreate.classList.remove('active');
                loadStats();
                loadData();
            } else {
                showToast(r.message, 'error');
            }
        } catch (e) {
            showToast('Terjadi kesalahan jaringan', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Buat Peminjaman';
        }
    }

    // ===== Toast =====
    function showToast(msg, type = 'success') {
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `<i class="fa-solid ${icon}"></i><span>${msg}</span>`;
        toastContainer.appendChild(t);
        setTimeout(() => {
            t.style.opacity = '0'; t.style.transform = 'translateX(40px)'; t.style.transition = '.3s ease';
            setTimeout(() => t.remove(), 300);
        }, 3500);
    }

    // ===== Helpers =====
    function esc(str) { const d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML; }
    function getInitials(name) {
        return (name || '').split(' ').map(w => w.charAt(0)).join('').substring(0, 2).toUpperCase();
    }
    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    // Add fadeIn keyframes
    const st = document.createElement('style');
    st.textContent = '@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(st);

    // ===== Expose =====
    window.APP = {
        detail: showDetail,
        closeDetail: () => modalDetail.classList.remove('active'),
        openApprove: openApproveModal,
        closeApprove: () => { modalApprove.classList.remove('active'); approveId = null; },
        openReject: openRejectModal,
        closeReject: () => { modalReject.classList.remove('active'); rejectId = null; },
        closeCreate: () => modalCreate.classList.remove('active'),
        page: (p) => { currentPage = p; loadData(); },
    };
})();
