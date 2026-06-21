/**
 * barang.js — Manajemen Barang Admin Panel
 * Handles CRUD operations, search, filter, pagination
 */
(function () {
    'use strict';

    const API = 'api_barang.php';
    let currentPage = 1;
    let editingId = null;
    let deleteId = null;
    let searchTimer = null;

    // ===== DOM References =====
    const $ = (sel) => document.querySelector(sel);
    const tableBody     = $('#tableBody');
    const pagination    = $('#pagination');
    const tableInfo     = $('#tableInfo');
    const searchInput   = $('#searchInput');
    const filterKat     = $('#filterKategori');
    const modalBarang   = $('#modalBarang');
    const modalDelete   = $('#modalDelete');
    const modalTitle    = $('#modalTitle');
    const formBarang    = $('#formBarang');
    const toastContainer = $('#toastContainer');

    // Stats
    const statTotal    = $('#statTotal');
    const statTersedia = $('#statTersedia');
    const statDipinjam = $('#statDipinjam');
    const statHabis    = $('#statHabis');

    // Form fields
    const fields = {
        id_barang:     $('#inpIdBarang'),
        nama_barang:   $('#inpNamaBarang'),
        kategori:      $('#inpKategori'),
        lokasi:        $('#inpLokasi'),
        stok_total:    $('#inpStokTotal'),
        stok_tersedia: $('#inpStokTersedia'),
        foto_barang:   $('#inpFotoBarang'),
    };

    // ===== Init =====
    loadStats();
    loadBarang();

    // ===== Event Listeners =====
    $('#btnAddBarang').addEventListener('click', () => openFormModal());
    $('#btnRefresh').addEventListener('click', () => { loadStats(); loadBarang(); });
    
    if ($('#statsTimeFilter')) {
        $('#statsTimeFilter').addEventListener('change', loadStats);
    }
    $('#modalClose').addEventListener('click', closeFormModal);
    $('#btnCancelForm').addEventListener('click', closeFormModal);
    $('#btnCancelDelete').addEventListener('click', closeDeleteModal);
    $('#btnConfirmDelete').addEventListener('click', confirmDelete);

    modalBarang.addEventListener('click', (e) => { if (e.target === modalBarang) closeFormModal(); });
    modalDelete.addEventListener('click', (e) => { if (e.target === modalDelete) closeDeleteModal(); });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { currentPage = 1; loadBarang(); }, 350);
    });
    filterKat.addEventListener('change', () => { currentPage = 1; loadBarang(); });

    formBarang.addEventListener('submit', (e) => {
        e.preventDefault();
        submitForm();
    });

    // Sidebar toggle
    const btnToggle = $('#btnToggleSidebar');
    const sidebar = $('#sidebar');
    const overlay = $('#sidebarOverlay');
    if (btnToggle) {
        btnToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // ===== API Helpers =====
    async function apiFetch(action, params = {}) {
        const url = new URL(API, window.location.href);
        url.searchParams.set('action', action);
        Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
        const res = await fetch(url);
        return res.json();
    }

    async function apiPost(action, body) {
        const url = new URL(API, window.location.href);
        url.searchParams.set('action', action);
        const options = { method: 'POST' };
        
        if (body instanceof FormData) {
            options.body = body;
        } else {
            options.headers = { 'Content-Type': 'application/json' };
            options.body = JSON.stringify(body);
        }
        
        const res = await fetch(url, options);
        return res.json();
    }

    // ===== Load Stats =====
    async function loadStats() {
        try {
            const timeFilter = $('#statsTimeFilter') ? $('#statsTimeFilter').value : 'all_time';
            const data = await apiFetch('stats', { time_filter: timeFilter });
            if (data.success) {
                animateNumber(statTotal, data.total);
                animateNumber(statTersedia, data.tersedia);
                animateNumber(statDipinjam, data.dipinjam);
                animateNumber(statHabis, data.habis);
                
                if ($('#labelTotal')) {
                    $('#labelTotal').textContent = timeFilter === 'bulan_ini' ? 'Barang Ditambahkan' : 'Total Barang';
                }
            }
        } catch (e) {
            console.error('Stats error', e);
        }
    }

    function animateNumber(el, target) {
        const duration = 600;
        const start = parseInt(el.textContent) || 0;
        const diff = target - start;
        if (diff === 0) { el.textContent = target; return; }
        const startTime = performance.now();
        function step(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(start + diff * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    // ===== Load Table =====
    async function loadBarang() {
        tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--outline)"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data…</td></tr>';

        try {
            const params = { page: currentPage };
            const search = searchInput.value.trim();
            const kat = filterKat.value;
            if (search) params.search = search;
            if (kat) params.kategori = kat;

            const data = await apiFetch('list', params);

            if (!data.success) throw new Error(data.message);

            if (data.data.length === 0) {
                tableBody.innerHTML = `
                    <tr><td colspan="8">
                        <div class="empty-state">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Tidak ada barang ditemukan</h3>
                            <p>Coba ubah filter atau tambahkan barang baru.</p>
                        </div>
                    </td></tr>`;
                tableInfo.textContent = '0 barang';
                pagination.innerHTML = '';
                return;
            }

            tableBody.innerHTML = data.data.map((b, i) => {
                const statusBadge = getStatusBadge(b.stok_tersedia, b.stok_total);
                const imgSrc = b.gambar ? b.gambar : 'https://via.placeholder.com/40?text=No+Img';
                return `
                <tr style="animation: fadeIn .3s ease ${i * .04}s both">
                    <td>
                        <div style="width:40px;height:40px;border-radius:var(--radius-sm);overflow:hidden;background:var(--surface-container-high);">
                            <img src="${imgSrc}" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='https://via.placeholder.com/40?text=No+Img'">
                        </div>
                    </td>
                    <td><span class="cell-id">${esc(b.id_barang)}</span></td>
                    <td><span class="cell-name">${esc(b.nama_barang)}</span></td>
                    <td><span class="badge badge-blue">${esc(b.kategori)}</span></td>
                    <td>${b.stok_total}</td>
                    <td>${b.stok_tersedia}</td>
                    <td>${esc(b.lokasi || '—')}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="actions-cell" style="justify-content:flex-end">
                            <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick="APP.edit('${esc(b.id_barang)}')">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm btn-icon" title="Hapus" onclick="APP.del('${esc(b.id_barang)}','${esc(b.nama_barang)}')" style="color:var(--error)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');

            // Info
            const from = (data.page - 1) * 10 + 1;
            const to = Math.min(data.page * 10, data.total);
            tableInfo.textContent = `Menampilkan ${from}–${to} dari ${data.total} barang`;

            // Pagination
            renderPagination(data.page, data.pages);

        } catch (e) {
            tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--error)"><i class="fa-solid fa-circle-exclamation"></i> ${e.message}</td></tr>`;
        }
    }

    function getStatusBadge(tersedia, total) {
        if (tersedia <= 0) return '<span class="badge badge-red"><i class="fa-solid fa-circle" style="font-size:.4rem"></i> Habis</span>';
        if (tersedia < total * 0.3) return '<span class="badge badge-amber"><i class="fa-solid fa-circle" style="font-size:.4rem"></i> Terbatas</span>';
        return '<span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:.4rem"></i> Tersedia</span>';
    }

    function renderPagination(current, total) {
        if (total <= 1) { pagination.innerHTML = ''; return; }
        let html = '';
        html += `<button class="page-btn" ${current <= 1 ? 'disabled' : ''} onclick="APP.page(${current - 1})"><i class="fa-solid fa-chevron-left" style="font-size:.65rem"></i></button>`;
        for (let i = 1; i <= total; i++) {
            if (total > 7 && i > 2 && i < total - 1 && Math.abs(i - current) > 1) {
                if (i === 3 || i === total - 2) html += '<span style="padding:0 .25rem;color:var(--outline)">…</span>';
                continue;
            }
            html += `<button class="page-btn ${i === current ? 'active' : ''}" onclick="APP.page(${i})">${i}</button>`;
        }
        html += `<button class="page-btn" ${current >= total ? 'disabled' : ''} onclick="APP.page(${current + 1})"><i class="fa-solid fa-chevron-right" style="font-size:.65rem"></i></button>`;
        pagination.innerHTML = html;
    }

    // ===== Modal: Add/Edit =====
    function openFormModal(data) {
        editingId = data ? data.id_barang : null;
        modalTitle.textContent = data ? 'Edit Barang' : 'Tambah Barang';
        clearFormErrors();

        if (data) {
            fields.id_barang.value = data.id_barang;
            fields.nama_barang.value = data.nama_barang;
            fields.kategori.value = data.kategori;
            fields.lokasi.value = data.lokasi || '';
            fields.stok_total.value = data.stok_total;
            fields.stok_tersedia.value = data.stok_tersedia;
            fields.foto_barang.value = ''; // clear file input on edit
        } else {
            formBarang.reset();
            fields.id_barang.value = ''; // Pastikan kosong saat tambah
            fields.stok_total.value = 0;
            fields.stok_tersedia.value = 0;
            fields.foto_barang.value = '';
        }

        // Kode barang selalu readonly karena auto-increment (create) atau PK (edit)
        fields.id_barang.readOnly = true;
        fields.id_barang.style.opacity = '.6';

        modalBarang.classList.add('active');
        setTimeout(() => fields.nama_barang.focus(), 200);
    }

    function closeFormModal() {
        modalBarang.classList.remove('active');
        editingId = null;
    }

    function clearFormErrors() {
        document.querySelectorAll('.form-group').forEach(g => g.classList.remove('has-error'));
    }

    // ===== Submit =====
    async function submitForm() {
        clearFormErrors();
        let valid = true;

        const body = new FormData();
        for (const [k, el] of Object.entries(fields)) {
            if (k === 'foto_barang') {
                if (el.files.length > 0) body.append('gambar', el.files[0]);
            } else {
                body.append(k, el.value.trim());
            }
        }
        
        const stokTotal = parseInt(fields.stok_total.value) || 0;
        const stokTersedia = parseInt(fields.stok_tersedia.value) || 0;

        // id_barang tidak perlu divalidasi di frontend karena readonly & auto-generated
        if (!fields.nama_barang.value.trim()) { setError('grpNamaBarang'); valid = false; }
        if (!fields.kategori.value.trim()) { setError('grpKategori'); valid = false; }
        if (stokTotal < 0) { setError('grpStokTotal'); valid = false; }
        if (stokTersedia > stokTotal) { setError('grpStokTersedia'); valid = false; }

        if (!valid) return;

        const btn = $('#btnSubmitForm');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan…';

        try {
            const action = editingId ? 'update' : 'create';
            const res = await apiPost(action, body);
            if (res.success) {
                showToast(res.message, 'success');
                closeFormModal();
                loadStats();
                loadBarang();
            } else {
                showToast(res.message, 'error');
            }
        } catch (e) {
            showToast('Terjadi kesalahan jaringan', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan';
        }
    }

    function setError(grpId) {
        document.getElementById(grpId).classList.add('has-error');
    }

    // ===== Edit =====
    async function editBarang(id) {
        try {
            const data = await apiFetch('list', { search: id });
            if (data.success && data.data.length) {
                openFormModal(data.data[0]);
            }
        } catch (e) {
            showToast('Gagal memuat data barang', 'error');
        }
    }

    // ===== Delete =====
    function openDeleteModal(id, nama) {
        deleteId = id;
        $('#deleteMsg').textContent = `"${nama}" akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.`;
        modalDelete.classList.add('active');
    }

    function closeDeleteModal() {
        modalDelete.classList.remove('active');
        deleteId = null;
    }

    async function confirmDelete() {
        if (!deleteId) return;
        const btn = $('#btnConfirmDelete');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus…';

        try {
            const res = await apiPost('delete', { id_barang: deleteId });
            if (res.success) {
                showToast(res.message, 'success');
                closeDeleteModal();
                loadStats();
                loadBarang();
            } else {
                showToast(res.message, 'error');
            }
        } catch (e) {
            showToast('Gagal menghapus barang', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Hapus';
        }
    }

    // ===== Toast =====
    function showToast(msg, type = 'success') {
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<i class="fa-solid ${icon}"></i><span>${msg}</span>`;
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(40px)';
            toast.style.transition = '.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // ===== Helpers =====
    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // ===== CSS animation for table rows =====
    const style = document.createElement('style');
    style.textContent = '@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(style);

    // ===== Expose for inline handlers =====
    window.APP = {
        edit: editBarang,
        del: openDeleteModal,
        page: (p) => { currentPage = p; loadBarang(); },
    };

})();
