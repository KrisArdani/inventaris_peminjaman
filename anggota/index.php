<?php
session_start();

// Cek apakah user sudah login dan rolenya anggota
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'anggota') {
    header("Location: ../login.php");
    exit;
}
$nama = htmlspecialchars($_SESSION['nama_lengkap']);
$initials = '';
foreach (explode(' ', $_SESSION['nama_lengkap']) as $w) $initials .= mb_substr($w, 0, 1);
$initials = mb_strtoupper(mb_substr($initials, 0, 2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Inventaris BEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="anggota.css?v=5.0">
</head>
<body>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon sidebar-brand-icon--img"><img src="../assets/images/logo bem.png" alt="Logo BEM KM Politeknik Purbaya"></div>
            <span>Inventaris BEM</span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Menu Utama</div>
            <a href="index.php" class="sidebar-link active">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="katalog.php" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Katalog Inventaris
            </a>
            <a href="form_peminjaman.php" class="sidebar-link">
                <i class="fa-solid fa-file-signature"></i> Form Pengajuan
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-avatar"><?= $initials ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= $nama ?></div>
                <div class="sidebar-user-role">Anggota BEM</div>
            </div>
            <a href="../logout.php" class="btn-logout" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="top-header-left">
                <button class="btn-sidebar-toggle" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
                <div class="breadcrumb">
                    <span class="current">Dashboard</span>
                </div>
            </div>
            <div class="top-header-right">
                <a href="form_peminjaman.php" class="cart-indicator-btn" title="Lihat Pengajuan">
                    <i class="fa-solid fa-file-signature"></i>
                </a>
            </div>
        </header>

        <!-- Page Container -->
        <div class="page-container">
            <!-- Welcome Section -->
            <div class="welcome-card">
                <div class="welcome-text">
                    <div class="katalog-badge" style="margin-bottom: 1rem;">
                        <i class="fa-solid fa-circle-user"></i> Panel Anggota
                    </div>
                    <h1>Halo, <?= htmlspecialchars(explode(' ', trim($_SESSION['nama_lengkap']))[0]); ?>! 👋</h1>
                    <p>Selamat datang di Dashboard Anggota BEM. Pantau status peminjaman, lihat riwayat, atau jelajahi katalog inventaris barang BEM Politeknik Purbaya.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <select class="filter-select" id="statsTimeFilter" style="background-color: var(--surface); border: 2px solid var(--primary); color: var(--primary); border-radius: 8px; padding: 0.75rem 1rem; font-family: inherit; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        <option value="all_time">Semua Waktu</option>
                        <option value="bulan_ini">Bulan Ini</option>
                    </select>
                    <a href="katalog.php" class="btn"><i class="fa-solid fa-layer-group"></i> Eksplor Inventaris</a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card" data-tooltip="Total keseluruhan peminjaman yang pernah Anda ajukan">
                    <div class="stat-icon icon-blue">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="labelTotal">Total Pengajuan</h3>
                        <div class="stat-value" id="statTotal">0</div>
                    </div>
                </div>
                <div class="stat-card" data-tooltip="Jumlah peminjaman yang sedang menunggu persetujuan admin">
                    <div class="stat-icon icon-yellow">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Menunggu Persetujuan</h3>
                        <div class="stat-value" id="statPending">0</div>
                    </div>
                </div>
                <div class="stat-card" data-tooltip="Barang yang saat ini sedang Anda pinjam dan belum dikembalikan">
                    <div class="stat-icon icon-green">
                        <i class="fa-solid fa-hand-holding-hand"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Sedang Dipinjam</h3>
                        <div class="stat-value" id="statAktif">0</div>
                    </div>
                </div>
                <div class="stat-card" data-tooltip="Barang yang belum Anda kembalikan melewati batas rencana kembali">
                    <div class="stat-icon icon-red">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Terlambat</h3>
                        <div class="stat-value" id="statTerlambat">0</div>
                    </div>
                </div>
            </div>

            <!-- History Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Riwayat Peminjaman</h2>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <select id="filterKembali" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; border-radius: 8px; cursor: pointer; background: #ffffff; border-color: var(--outline-variant);">
                            <option value="all">Semua Status Kembali</option>
                            <option value="dikembalikan">Sudah Dikembalikan</option>
                            <option value="belum">Belum Dikembalikan</option>
                        </select>
                        <button class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="loadRiwayat()"><i class="fa-solid fa-rotate-right"></i> Segarkan</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="riwayatTable">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Tanggal & Waktu</th>
                                <th>Detail Barang</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="riwayatBody">
                            <tr><td colspan="5" style="text-align: center; padding: 3rem;"><i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color: var(--primary); margin-bottom: 1rem;"></i><br>Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Peminjaman -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-close" onclick="closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </div>
            
            <div class="detail-header">
                <h2 style="margin: 0; font-size: 1.35rem; font-weight: 800; color: #0f172a;">Detail Peminjaman</h2>
                <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
                    <span class="text-muted" id="detIdPeminjaman" style="font-family: monospace; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; color: var(--primary-dark);"></span>
                </div>
            </div>

            <div id="alasanTolakContainer" style="display: none; margin: 1rem 2rem 0;"></div>

            <div class="detail-info">
                <div>
                    <strong>Waktu Pengajuan</strong>
                    <span id="detTglPengajuan"></span>
                </div>
                <div>
                    <strong>Status Persetujuan</strong>
                    <div id="detStatusApproval" style="margin-top: 0.25rem;"></div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <strong>Nama Kegiatan</strong>
                    <span id="detKegiatan"></span>
                </div>
                <div style="grid-column: 1 / -1;">
                    <strong>Tujuan / Keperluan</strong>
                    <span id="detTujuan"></span>
                </div>
                <div style="grid-column: 1 / -1;">
                    <strong>Lokasi Penggunaan</strong>
                    <span id="detLokasi"></span>
                </div>
            </div>

            <div style="padding: 1.5rem 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem; color: #334155;">Daftar Barang</h3>
                <div class="table-responsive" style="border: 1px solid var(--outline-variant); border-radius: 12px; overflow: hidden;">
                    <table style="width: 100%; font-size: 0.9rem;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="padding: 0.75rem 1rem;">Barang</th>
                                <th style="padding: 0.75rem 1rem;">Jml</th>
                                <th style="padding: 0.75rem 1rem;">Periode Pinjam</th>
                                <th style="padding: 0.75rem 1rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="detItemsBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            loadRiwayat();

            // Sidebar toggle
            const btnToggle = document.getElementById('btnToggleSidebar');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
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
        });

        function getStatusBadge(status) {
            if (status === 'pending') return '<span class="status-badge status-pending"><i class="fa-solid fa-clock"></i> Pending</span>';
            if (status === 'disetujui') return '<span class="status-badge status-disetujui"><i class="fa-solid fa-check"></i> Disetujui</span>';
            if (status === 'ditolak') return '<span class="status-badge status-ditolak"><i class="fa-solid fa-xmark"></i> Ditolak</span>';
            return `<span class="status-badge">${status}</span>`;
        }

        function getReturnStatusBadge(p) {
            if (p.status_approval !== 'disetujui') return '';
            const total = parseInt(p.jumlah_item) || 0;
            const kembali = parseInt(p.jumlah_dikembalikan) || 0;
            if (total === 0) return '';
            if (kembali === total) {
                return '<span class="status-badge" style="background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0;"><i class="fa-solid fa-box-archive"></i> Dikembalikan</span>';
            } else {
                return '<span class="status-badge" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;"><i class="fa-solid fa-box-open"></i> Belum Dikembalikan</span>';
            }
        }

        async function loadStats() {
            try {
                const timeFilter = document.getElementById('statsTimeFilter') ? document.getElementById('statsTimeFilter').value : 'all_time';
                const res = await fetch('api_anggota.php?action=stats&time_filter=' + timeFilter);
                const data = await res.json();
                if (data.success) {
                    animateValue('statTotal', 0, data.total, 1000);
                    animateValue('statPending', 0, data.pending, 1000);
                    animateValue('statAktif', 0, data.aktif, 1000);
                    animateValue('statTerlambat', 0, data.terlambat, 1000);
                    
                    if(document.getElementById('labelTotal')) {
                        document.getElementById('labelTotal').textContent = timeFilter === 'bulan_ini' ? 'Pengajuan (Bulan Ini)' : 'Total Pengajuan';
                    }
                }
            } catch (err) { console.error('Failed to load stats', err); }
        }

        function animateValue(id, start, end, duration) {
            if (start === end) { document.getElementById(id).innerHTML = end; return; }
            let range = end - start;
            let current = start;
            let increment = end > start ? 1 : -1;
            let stepTime = Math.abs(Math.floor(duration / range));
            let obj = document.getElementById(id);
            let timer = setInterval(function() {
                current += increment;
                obj.innerHTML = current;
                if (current == end) clearInterval(timer);
            }, stepTime);
        }

        let riwayatData = [];
        async function loadRiwayat() {
            const tbody = document.getElementById('riwayatBody');
            tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 3rem;"><i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color: var(--primary); margin-bottom: 1rem;"></i><br>Memuat data...</td></tr>`;
            try {
                const res = await fetch('api_anggota.php?action=riwayat');
                const data = await res.json();
                if (data.success) {
                    riwayatData = data.data;
                    renderRiwayat();
                }
            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="5" style="color: #ef4444; text-align: center; padding: 2rem;">Gagal memuat data.</td></tr>`;
            }
        }

        function renderRiwayat() {
            const tbody = document.getElementById('riwayatBody');
            const filterValue = document.getElementById('filterKembali').value;
            let filtered = riwayatData;
            if (filterValue === 'dikembalikan') filtered = riwayatData.filter(p => p.status_approval === 'disetujui' && parseInt(p.jumlah_dikembalikan) === parseInt(p.jumlah_item));
            else if (filterValue === 'belum') filtered = riwayatData.filter(p => p.status_approval === 'disetujui' && parseInt(p.jumlah_dikembalikan) < parseInt(p.jumlah_item));

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 4rem; color: #64748b;">Data tidak ditemukan.</td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(p => {
                const dateObj = new Date(p.tgl_pengajuan);
                const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                const timeStr = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                return `
                <tr>
                    <td><strong style="font-family: monospace; color: var(--primary-dark); font-weight: 700;">${p.id_peminjaman}</strong></td>
                    <td>
                        <div class="item-list">
                            <span class="item-name">${dateStr}</span>
                            <span class="item-meta">${timeStr}</span>
                        </div>
                    </td>
                    <td>
                        <div class="item-list">
                            <span class="item-name" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;" title="${p.daftar_barang}">${p.daftar_barang}</span>
                            <span class="item-meta">${p.jumlah_item} jenis barang</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.4rem; align-items: flex-start;">
                            ${getStatusBadge(p.status_approval)}
                            ${getReturnStatusBadge(p)}
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <button class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;" onclick="viewDetail('${p.id_peminjaman}')">
                            Detail
                        </button>
                    </td>
                </tr>`;
            }).join('');
        }

        document.getElementById('filterKembali').addEventListener('change', renderRiwayat);

        async function viewDetail(id) {
            try {
                const res = await fetch(`api_anggota.php?action=detail_riwayat&id=${id}`);
                const data = await res.json();
                if (data.success) {
                    const p = data.peminjaman;
                    document.getElementById('detIdPeminjaman').textContent = p.id_peminjaman;
                    
                    const dateObj = new Date(p.tgl_pengajuan);
                    document.getElementById('detTglPengajuan').innerHTML = `${dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })} <span style="color:#64748b; font-weight:normal; font-size:0.85rem;">pukul ${dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span>`;
                    
                    document.getElementById('detStatusApproval').innerHTML = `
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.25rem;">
                            ${getStatusBadge(p.status_approval)}
                            ${getReturnStatusBadge(p)}
                        </div>
                    `;
                    document.getElementById('detKegiatan').textContent = p.nama_kegiatan || '-';
                    document.getElementById('detTujuan').textContent = p.tujuan || '-';
                    document.getElementById('detLokasi').textContent = p.lokasi || '-';

                    const tolakCont = document.getElementById('alasanTolakContainer');
                    if (p.status_approval === 'ditolak' && p.alasan_tolak) {
                        tolakCont.style.display = 'block';
                        tolakCont.className = 'alasan-tolak';
                        tolakCont.innerHTML = `<strong style="display:block; margin-bottom:0.25rem;">Alasan Penolakan:</strong>${p.alasan_tolak}`;
                    } else {
                        tolakCont.style.display = 'none';
                    }

                    const tbody = document.getElementById('detItemsBody');
                    tbody.innerHTML = data.items.map(item => {
                        let statusHtml = '';
                        if (item.status_item === 'dipinjam') statusHtml = '<span class="status-badge" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; font-size: 0.7rem;"><i class="fa-solid fa-hourglass-start"></i> Dipinjam</span>';
                        else if (item.status_item === 'terlambat') statusHtml = '<span class="status-badge" style="background:#fef2f2; color:#ef4444; border:1px solid #fecaca; font-size: 0.7rem;"><i class="fa-solid fa-circle-exclamation"></i> Terlambat</span>';
                        else if (item.status_item === 'dikembalikan') statusHtml = '<span class="status-badge" style="background:#f0fdf4; color:#10b981; border:1px solid #bbf7d0; font-size: 0.7rem;"><i class="fa-solid fa-circle-check"></i> Dikembalikan</span>';

                        const tglPinjam = new Date(item.tgl_pinjam).toLocaleDateString('id-ID', {day:'numeric', month:'short'});
                        const tglKembali = new Date(item.tgl_kembali_rencana).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'});

                        return `
                        <tr>
                            <td style="padding: 0.75rem 1rem;">
                                <strong style="color: #0f172a; display:block;">${item.nama_barang}</strong>
                                <small style="color: #64748b;">${item.kategori}</small>
                            </td>
                            <td style="padding: 0.75rem 1rem; font-weight: 600;">${item.jumlah}</td>
                            <td style="padding: 0.75rem 1rem; color: #475569; font-size: 0.85rem;">
                                ${tglPinjam} - ${tglKembali}
                            </td>
                            <td style="padding: 0.75rem 1rem;">${statusHtml}</td>
                        </tr>
                        `;
                    }).join('');

                    document.getElementById('detailModal').classList.add('active');
                }
            } catch (err) { console.error(err); }
        }

        function closeModal() { document.getElementById('detailModal').classList.remove('active'); }
        window.onclick = function(event) { if (event.target == document.getElementById('detailModal')) closeModal(); }
        
        if (document.getElementById('statsTimeFilter')) {
            document.getElementById('statsTimeFilter').addEventListener('change', loadStats);
        }
    </script>
</body>
</html>
