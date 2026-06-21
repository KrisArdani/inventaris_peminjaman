(function() {
    // ===== DOM Elements =====
    const $ = id => document.querySelector(id);
    
    // Sidebar
    const btnToggleSidebar = $('#btnToggleSidebar');
    const sidebar = $('#sidebar');
    const sidebarOverlay = $('#sidebarOverlay');
    
    // Table & Controls
    const searchInput = $('#searchInput');
    const roleFilter = $('#roleFilter');
    const tableBody = $('#tableBody');
    const pagination = $('#pagination');
    
    // Modals
    const modalUser = $('#modalUser');
    const modalTitle = $('#modalTitle');
    const formUser = $('#formUser');
    const passReq = $('#passReq');
    
    const modalDelete = $('#modalDelete');
    const toastContainer = $('#toastContainer');

    // Fields
    const fields = {
        nama_lengkap: $('#inpNama'),
        username: $('#inpUsername'),
        role: $('#inpRole'),
        password: $('#inpPassword'),
        no_hp: $('#inpNoHp'),
        asal_ormawa: $('#inpOrmawa')
    };

    // State
    let currentPage = 1;
    let searchTimer = null;
    let editingId = null;
    let deleteId = null;

    // ===== Init =====
    loadStats();
    loadUsers();

    // ===== Event Listeners =====
    if (btnToggleSidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }

    $('#btnAddUser').addEventListener('click', () => openFormModal());
    if ($('#statsTimeFilter')) {
        $('#statsTimeFilter').addEventListener('change', loadStats);
    }
    $('#btnRefresh').addEventListener('click', () => { loadStats(); loadUsers(); });
    $('#modalClose').addEventListener('click', closeFormModal);
    $('#btnCancelForm').addEventListener('click', closeFormModal);
    $('#btnCancelDelete').addEventListener('click', closeDeleteModal);
    $('#btnConfirmDelete').addEventListener('click', confirmDelete);

    modalUser.addEventListener('click', (e) => { if (e.target === modalUser) closeFormModal(); });
    modalDelete.addEventListener('click', (e) => { if (e.target === modalDelete) closeDeleteModal(); });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { currentPage = 1; loadUsers(); }, 300);
    });

    roleFilter.addEventListener('change', () => { currentPage = 1; loadUsers(); });

    formUser.addEventListener('submit', (e) => {
        e.preventDefault();
        submitForm();
    });

    // ===== API Functions =====
    async function apiFetch(action, params = {}) {
        const url = new URL('api_user.php', window.location.href);
        url.searchParams.append('action', action);
        for (const key in params) url.searchParams.append(key, params[key]);
        const res = await fetch(url);
        return await res.json();
    }

    async function apiPost(action, body) {
        const res = await fetch(`api_user.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        return await res.json();
    }

    // ===== Load Data =====
    async function loadStats() {
        try {
            const timeFilter = $('#statsTimeFilter') ? $('#statsTimeFilter').value : 'all_time';
            const res = await apiFetch('stats', { time_filter: timeFilter });
            if (res.success) {
                anim($('#statTotal'), res.total);
                anim($('#statAnggota'), res.anggota);
                anim($('#statAdmin'), res.admin);
                
                if ($('#labelTotal')) {
                    $('#labelTotal').textContent = timeFilter === 'bulan_ini' ? 'Pengguna Baru' : 'Total Pengguna';
                }
            }
        } catch (e) {
            console.error('Failed to load stats', e);
        }
    }

    function anim(el, val) {
        if (!el) return;
        el.textContent = val;
    }

    async function loadUsers() {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--outline)"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data...</td></tr>';
        try {
            const data = await apiFetch('list', {
                search: searchInput.value.trim(),
                role: roleFilter.value,
                page: currentPage
            });

            if (!data.success) throw new Error(data.message);

            if (data.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--outline)">Tidak ada data pengguna ditemukan.</td></tr>';
                pagination.innerHTML = '';
                return;
            }

            tableBody.innerHTML = data.data.map((u, i) => {
                const initials = u.nama_lengkap.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                return `
                <tr style="animation: fadeIn .3s ease ${i * .04}s both">
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">${initials}</div>
                            <div class="user-details">
                                <span class="user-name">${esc(u.nama_lengkap)}</span>
                                <span class="user-username">@${esc(u.username)}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="role-badge role-${u.role}">${u.role}</span></td>
                    <td>${u.no_hp ? esc(u.no_hp) : '<span style="color:var(--outline)">-</span>'}</td>
                    <td>${u.asal_ormawa ? esc(u.asal_ormawa) : '<span style="color:var(--outline)">-</span>'}</td>
                    <td style="color:var(--outline);font-size:0.85rem">${u.created_at.substring(0, 10)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" title="Edit" onclick="APP.edit('${u.id_user}', '${esc(u.nama_lengkap)}', '${esc(u.username)}', '${u.role}', '${esc(u.no_hp||'')}', '${esc(u.asal_ormawa||'')}')"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button class="btn-action btn-delete" title="Hapus" onclick="APP.del('${u.id_user}', '${esc(u.nama_lengkap)}')"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                    </td>
                </tr>
                `;
            }).join('');

            renderPagination(data.total, data.limit, data.page);
        } catch (e) {
            tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--error)"><i class="fa-solid fa-circle-exclamation"></i> Gagal memuat data</td></tr>`;
        }
    }

    function renderPagination(totalItems, limit, current) {
        const total = Math.ceil(totalItems / limit);
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
        editingId = data ? data.id_user : null;
        modalTitle.textContent = data ? 'Edit Pengguna' : 'Tambah Pengguna';
        clearFormErrors();

        if (data) {
            fields.nama_lengkap.value = data.nama_lengkap;
            fields.username.value = data.username;
            fields.role.value = data.role;
            fields.password.value = '';
            fields.no_hp.value = data.no_hp;
            fields.asal_ormawa.value = data.asal_ormawa;
            passReq.style.display = 'none'; // Optional on edit
        } else {
            formUser.reset();
            passReq.style.display = 'inline'; // Required on create
        }

        modalUser.classList.add('active');
        setTimeout(() => fields.nama_lengkap.focus(), 200);
    }

    window.openFormModal = openFormModal; // Export for edit inline

    function closeFormModal() {
        modalUser.classList.remove('active');
        editingId = null;
    }

    function clearFormErrors() {
        document.querySelectorAll('.form-group').forEach(g => g.classList.remove('has-error'));
    }

    function setError(grpId) {
        document.getElementById(grpId).classList.add('has-error');
    }

    // ===== Submit =====
    async function submitForm() {
        clearFormErrors();
        let valid = true;

        if (!fields.nama_lengkap.value.trim()) { setError('grpNama'); valid = false; }
        if (!fields.username.value.trim()) { setError('grpUsername'); valid = false; }
        if (!fields.role.value.trim()) { setError('grpRole'); valid = false; }
        if (!editingId && !fields.password.value.trim()) { setError('grpPassword'); valid = false; }

        if (!valid) return;

        const btn = $('#btnSubmitForm');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan…';

        const body = {
            id_user: editingId,
            nama_lengkap: fields.nama_lengkap.value.trim(),
            username: fields.username.value.trim(),
            role: fields.role.value,
            password: fields.password.value, // will be ignored by backend if empty on update
            no_hp: fields.no_hp.value.trim(),
            asal_ormawa: fields.asal_ormawa.value.trim()
        };

        try {
            const action = editingId ? 'update' : 'create';
            const res = await apiPost(action, body);
            if (res.success) {
                showToast(res.message, 'success');
                closeFormModal();
                loadStats();
                loadUsers();
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
            const res = await apiPost('delete', { id_user: deleteId });
            if (res.success) {
                showToast(res.message, 'success');
                closeDeleteModal();
                loadStats();
                loadUsers();
            } else {
                showToast(res.message, 'error');
            }
        } catch (e) {
            showToast('Gagal menghapus user', 'error');
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
    if (!document.querySelector('#fadeInStyle')) {
        const style = document.createElement('style');
        style.id = 'fadeInStyle';
        style.textContent = '@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
        document.head.appendChild(style);
    }

    // ===== Expose for inline handlers =====
    window.APP = {
        edit: (id, nama, user, role, hp, ormawa) => openFormModal({id_user: id, nama_lengkap: nama, username: user, role: role, no_hp: hp, asal_ormawa: ormawa}),
        del: openDeleteModal,
        page: (p) => { currentPage = p; loadUsers(); },
    };

})();
