<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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
    <title>Dashboard Admin — Inventaris BEM</title>
    <meta name="description" content="Dashboard admin sistem inventaris dan peminjaman barang BEM Politeknik Purbaya">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=6">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <a href="barang.php" class="sidebar-link">
            <i class="fa-solid fa-box-archive"></i> Manajemen Barang
        </a>
        <a href="peminjaman.php" class="sidebar-link">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Peminjaman
        </a>
        <a href="pengembalian.php" class="sidebar-link">
            <i class="fa-solid fa-rotate-left"></i> Pengembalian
        </a>

        <div class="sidebar-section-label">Pengaturan</div>
        <a href="user.php" class="sidebar-link">
            <i class="fa-solid fa-users"></i> Kelola User
        </a>
        <a href="laporan.php" class="sidebar-link">
            <i class="fa-solid fa-chart-pie"></i> Laporan
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-avatar"><?= $initials ?></div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= $nama ?></div>
            <div class="sidebar-user-role">Administrator</div>
        </div>
        <a href="../logout.php" class="btn-ghost" title="Logout" style="color:rgba(255,255,255,.5)">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</aside>

<!-- ===== MAIN ===== -->
<div class="main-content">
    <header class="top-header">
        <div class="top-header-left">
            <button class="btn-sidebar-toggle" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="breadcrumb">
                <span class="current">Dashboard</span>
            </div>
        </div>
        <div class="top-header-right">
            <button class="header-icon-btn" title="Notifikasi">
                <i class="fa-regular fa-bell"></i>
                <span class="notif-dot"></span>
            </button>
        </div>
    </header>

    <div class="page-container">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="page-title">Dashboard Admin</h1>
                <p class="page-subtitle">Pantau ringkasan inventaris dan aktivitas peminjaman.</p>
            </div>
            <div class="header-actions">
                <select class="filter-select" id="statsTimeFilter" style="background-color: var(--surface); border: 2px solid var(--primary); color: var(--primary); border-radius: 8px; padding: 0.5rem 1rem; font-family: inherit; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <option value="all_time">Semua Waktu</option>
                    <option value="bulan_ini">Bulan Ini</option>
                </select>
            </div>
        </div>

        <div class="stats-row" id="statsRow">
            <div class="stat-card" data-tooltip="Keseluruhan item barang yang tercatat di inventaris">
                <div class="stat-icon green"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div><div class="stat-value" id="statTotal">—</div><div class="stat-label" id="labelTotal">Total Barang</div></div>
            </div>
            <div class="stat-card" data-tooltip="Sisa fisik stok barang yang siap dipinjam saat ini">
                <div class="stat-icon blue"><i class="fa-solid fa-check-circle"></i></div>
                <div><div class="stat-value" id="statTersedia">—</div><div class="stat-label">Stok Tersedia</div></div>
            </div>
            <div class="stat-card" data-tooltip="Total fisik stok barang yang sedang di luar/dipinjam saat ini">
                <div class="stat-icon amber"><i class="fa-solid fa-hand-holding-hand"></i></div>
                <div><div class="stat-value" id="statDipinjam">—</div><div class="stat-label">Sedang Dipinjam</div></div>
            </div>
            <div class="stat-card" data-tooltip="Total transaksi peminjaman yang sedang aktif atau terlambat">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a"><i class="fa-solid fa-file-signature"></i></div>
                <div><div class="stat-value" id="statPinjamanAktif">—</div><div class="stat-label">Pinjaman Aktif</div></div>
            </div>
        </div>

        <!-- Quick access cards -->
        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;margin-top:1rem">Akses Cepat</h2>
        <div class="stats-row">
            <a href="barang.php" class="stat-card" style="cursor:pointer;text-decoration:none" data-tooltip="Pergi ke halaman Manajemen Barang">
                <div class="stat-icon amber"><i class="fa-solid fa-boxes-packing"></i></div>
                <div><div style="font-weight:600;font-size:.9rem">Kelola Barang</div><div class="stat-label">Tambah atau edit inventaris</div></div>
            </a>
            <a href="peminjaman.php" class="stat-card" style="cursor:pointer;text-decoration:none" data-tooltip="Pergi ke halaman Manajemen Peminjaman">
                <div class="stat-icon emerald"><i class="fa-solid fa-clipboard-check"></i></div>
                <div><div style="font-weight:600;font-size:.9rem">Peminjaman</div><div class="stat-label">Kelola pengajuan masuk</div></div>
            </a>
            <a href="pengembalian.php" class="stat-card" style="cursor:pointer;text-decoration:none" data-tooltip="Pergi ke halaman Manajemen Pengembalian">
                <div class="stat-icon blue"><i class="fa-solid fa-rotate-left"></i></div>
                <div><div style="font-weight:600;font-size:.9rem">Pengembalian</div><div class="stat-label">Kelola pengembalian barang</div></div>
            </a>
        </div>

        <!-- Charts Row -->
        <div class="charts-grid">
            <div class="chart-card full-width">
                <div class="chart-header">
                    <h3 class="chart-title">Tren Peminjaman Barang</h3>
                    <span class="chart-subtitle">Statistik peminjaman 6 bulan terakhir</span>
                </div>
                <div class="chart-body">
                    <canvas id="chartTrenPeminjaman"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header" style="margin-top: 1.5rem;">
                    <h3 class="chart-title">Top 5 Barang Terpopuler</h3>
                    <span class="chart-subtitle">Barang paling sering dipinjam</span>
                </div>
                <div class="chart-body">
                    <canvas id="chartBarangTerpopuler"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header" style="margin-top: 1.5rem;">
                    <h3 class="chart-title">Distribusi Kategori Barang</h3>
                    <span class="chart-subtitle">Berdasarkan kategori inventaris</span>
                </div>
                <div class="chart-body">
                    <canvas id="chartKategoriBarang"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // Sidebar toggle
    const btn = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btn){
        btn.addEventListener('click',()=>{sidebar.classList.toggle('open');overlay.classList.toggle('active')});
        overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('active')});
    }
    // Load Data Stats
    function loadDashboardStats() {
        const timeFilter = document.getElementById('statsTimeFilter') ? document.getElementById('statsTimeFilter').value : 'all_time';
        fetch('api_barang.php?action=stats&time_filter=' + timeFilter)
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    anim(document.getElementById('statTotal'), res.total);
                    anim(document.getElementById('statTersedia'), res.tersedia);
                    anim(document.getElementById('statDipinjam'), res.dipinjam);
                    anim(document.getElementById('statPinjamanAktif'), res.pinjaman_aktif);
                    
                    if(document.getElementById('labelTotal')) {
                        document.getElementById('labelTotal').textContent = timeFilter === 'bulan_ini' ? 'Barang Ditambahkan' : 'Total Barang';
                    }
                }
            });
    }
    
    loadDashboardStats();
    if(document.getElementById('statsTimeFilter')) {
        document.getElementById('statsTimeFilter').addEventListener('change', loadDashboardStats);
    }
    function anim(el,target){
        const dur=600,start=parseInt(el.textContent)||0,diff=target-start;
        if(!diff){el.textContent=target;return}
        const t0=performance.now();
        function step(now){
            const p=Math.min((now-t0)/dur,1);
            el.textContent=Math.round(start+diff*(1-Math.pow(1-p,3)));
            if(p<1)requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    // Helper to format months in Indonesian
    function formatMonth(dateStr) {
        const parts = dateStr.split('-');
        if (parts.length !== 2) return dateStr;
        const year = parts[0];
        const monthNum = parseInt(parts[1], 10);
        const monthsIndo = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];
        return monthsIndo[monthNum - 1] + " " + year;
    }

    // Load and render charts
    fetch('api_barang.php?action=chart_data')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                renderTrenChart(res.tren);
                renderTopChart(res.top);
                renderKategoriChart(res.kategori);
            }
        });

    function renderTrenChart(data) {
        const labels = data.map(item => formatMonth(item.bulan));
        const values = data.map(item => item.jumlah);

        const ctx = document.getElementById('chartTrenPeminjaman').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: values,
                    borderColor: '#386641',
                    backgroundColor: 'rgba(56, 102, 65, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#386641',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });
    }

    function renderTopChart(data) {
        const labels = data.map(item => item.nama_barang);
        const values = data.map(item => item.total_dipinjam);

        const ctx = document.getElementById('chartBarangTerpopuler').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Kali Dipinjam',
                    data: values,
                    backgroundColor: [
                        '#214a2d', '#386641', '#4a7c59', '#6a994e', '#a3b18a'
                    ],
                    borderRadius: 6,
                    maxBarThickness: 32
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { borderDash: [4, 4] }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    function renderKategoriChart(data) {
        const labels = data.map(item => item.kategori);
        const values = data.map(item => item.jumlah);

        const ctx = document.getElementById('chartKategoriBarang').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#386641', '#adedd3'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Manrope', size: 11 }
                        }
                    }
                }
            }
        });
    }
})();
</script>
</body>
</html>
