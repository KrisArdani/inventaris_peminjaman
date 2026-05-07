/**
 * pengembalian.js — Manajemen Pengembalian Admin Panel
 */
(function () {
    'use strict';

    const API = 'api_pengembalian.php';
    let currentPage = 1;
    let searchTimer = null;
    let currentData = [];

    const $ = (s) => document.querySelector(s);
    const tableBody      = $('#tableBody');
    const pagination     = $('#pagination');
    const tableInfo      = $('#tableInfo');
    const searchInput    = $('#searchInput');
    const filterStatus   = $('#filterStatus');
    const toastContainer = $('#toastContainer');

    // Modals
    const modalProses = $('#modalProses');
    const formProses  = $('#formProses');
    const modalDetail = $('#modalDetail');

    // ===== Init =====
    loadStats();
    loadData();

    // ===== Events =====
    $('#btnRefresh').addEventListener('click', () => { loadStats(); loadData(); });
    formProses.addEventListener('submit', (e) => { e.preventDefault(); submitProses(); });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { currentPage = 1; loadData(); }, 350);
    });
    filterStatus.addEventListener('change', () => { currentPage = 1; loadData(); });

    // Close modals on overlay click
    [modalProses, modalDetail].forEach(m => {
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

    // ===== Formatter =====
    const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);

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
                anim($('#statAktif'), d.aktif);
                anim($('#statTerlambat'), d.terlambat);
                anim($('#statDikembalikan'), d.dikembalikan);
                $('#statDenda').textContent = formatCurrency(d.denda || 0);
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
            const params = { page: currentPage, status: filterStatus.value };
            const s = searchInput.value.trim();
            if (s) params.search = s;

            const d = await apiFetch('list', params);
            if (!d.success) throw new Error(d.message);

            currentData = d.data;

            if (d.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-box-open"></i><h3>Tidak ada data</h3><p>Tidak ada data pengembalian yang sesuai.</p></div></td></tr>';
                tableInfo.textContent = '0 item';
                pagination.innerHTML = '';
                return;
            }

            tableBody.innerHTML = d.data.map((r, i) => {
                const ini = getInitials(r.nama_lengkap);
                const tglBatas = formatDate(r.tgl_kembali_rencana);
                const statusHTML = statusBadge(r.status_item);
                
                let aksiBtn = '';
                if (['dipinjam', 'terlambat'].includes(r.status_item)) {
                    aksiBtn = `<button class="btn btn-primary btn-sm" onclick="APP.openProses(${i})">
                                  <i class="fa-solid fa-rotate-left"></i> Proses
                               </button>`;
                } else {
                    aksiBtn = `<button class="btn btn-ghost btn-sm btn-icon" title="Lihat Bukti" onclick="APP.openDetail(${i})">
                                  <i class="fa-solid fa-receipt"></i>
                               </button>`;
                }

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
                    <td><div style="font-weight:600">${esc(r.nama_barang)}</div><div style="font-size:0.75rem;color:var(--outline)">ID: ${esc(r.id_barang)}</div></td>
                    <td style="text-align:center">${r.jumlah}</td>
                    <td>${tglBatas}</td>
                    <td>${statusHTML}</td>
                    <td>
                        <div class="actions-cell" style="justify-content:flex-end">
                            ${aksiBtn}
                        </div>
                    </td>
                </tr>`;
            }).join('');

            const from = (d.page - 1) * 10 + 1;
            const to = Math.min(d.page * 10, d.total);
            tableInfo.textContent = `Menampilkan ${from}–${to} dari ${d.total} item`;
            renderPagination(d.page, d.pages);
        } catch (e) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--error)"><i class="fa-solid fa-circle-exclamation"></i> ${e.message}</td></tr>`;
        }
    }

    function statusBadge(s) {
        const map = {
            dipinjam:      { cls: 'badge-blue',  icon: 'fa-arrow-up-right-from-square', text: 'Dipinjam' },
            dikembalikan:  { cls: 'badge-green', icon: 'fa-check-double',               text: 'Dikembalikan' },
            terlambat:     { cls: 'badge-red',   icon: 'fa-clock',                      text: 'Terlambat' },
        };
        const m = map[s] || map.dipinjam;
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

    // ===== Proses Pengembalian =====
    function openProsesModal(index) {
        const item = currentData[index];
        if (!item) return;

        $('#inpDetailId').value = item.id_detail;
        $('#lblPeminjam').textContent = `${item.nama_lengkap} (@${item.username})`;
        $('#lblBarang').textContent = `${item.nama_barang} (Qty: ${item.jumlah})`;
        $('#lblStatus').innerHTML = statusBadge(item.status_item);
        $('#lblTglKembali').textContent = formatDate(item.tgl_kembali_rencana);

        $('#inpKondisi').value = 'Baik';
        $('#inpDenda').value = 0;

        modalProses.classList.add('active');
        setTimeout(() => $('#inpKondisi').focus(), 200);
    }

    async function submitProses() {
        const id_detail = $('#inpDetailId').value;
        const kondisi = $('#inpKondisi').value;
        const denda = parseFloat($('#inpDenda').value) || 0;

        const btn = $('#btnSubmitProses');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses…';

        try {
            const r = await apiPost('proses', { id_detail, kondisi, denda });
            if (r.success) {
                showToast(r.message, 'success');
                modalProses.classList.remove('active');
                loadStats();
                loadData();
            } else {
                showToast(r.message, 'error');
            }
        } catch (e) {
            showToast('Terjadi kesalahan jaringan', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Selesaikan Pengembalian';
        }
    }

    // ===== Detail Riwayat Modal =====
    function openDetailModal(index) {
        const item = currentData[index];
        if (!item) return;

        const dendaHTML = parseFloat(item.denda) > 0 
            ? `<span style="color:var(--error);font-weight:700">${formatCurrency(item.denda)}</span>`
            : `<span style="color:var(--outline)">Tidak ada denda</span>`;

        $('#detailRiwayatContent').innerHTML = `
            <div class="detail-info" style="margin-bottom:1rem">
                <div class="detail-field">
                    <span class="detail-label">ID Transaksi</span>
                    <span class="detail-value" style="font-family:monospace">${esc(item.id_peminjaman)}</span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Peminjam</span>
                    <span class="detail-value">${esc(item.nama_lengkap)}</span>
                </div>
                <div class="detail-field full">
                    <span class="detail-label">Barang Dikembalikan</span>
                    <span class="detail-value">${esc(item.nama_barang)} (Qty: ${item.jumlah})</span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Tgl. Kembali Aktual</span>
                    <span class="detail-value">${formatDate(item.tgl_kembali_asli)}</span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Penerima (Admin)</span>
                    <span class="detail-value">${esc(item.nama_admin_penerima || 'Sistem')}</span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Kondisi Barang</span>
                    <span class="detail-value">${esc(item.kondisi_barang)}</span>
                </div>
                <div class="detail-field">
                    <span class="detail-label">Denda</span>
                    <span class="detail-value">${dendaHTML}</span>
                </div>
            </div>
        `;

        modalDetail.classList.add('active');
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
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit' });
    }

    // Add fadeIn keyframes
    const st = document.createElement('style');
    st.textContent = '@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(st);

    // ===== Expose =====
    window.APP = {
        openProses: openProsesModal,
        closeProses: () => modalProses.classList.remove('active'),
        openDetail: openDetailModal,
        closeDetail: () => modalDetail.classList.remove('active'),
        page: (p) => { currentPage = p; loadData(); },
    };
})();
