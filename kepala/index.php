<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala') {
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
    <title>Dashboard Kepala — Inventaris BEM</title>
    <meta name="description" content="Dashboard kepala sistem inventaris dan peminjaman barang BEM Politeknik Purbaya">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/admin.css">
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
        <span>Inventaris BEM</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        <a href="index.php" class="sidebar-link active">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        
        <div class="sidebar-section-label">Laporan</div>
        <a href="laporan.php" class="sidebar-link">
            <i class="fa-solid fa-chart-pie"></i> Laporan
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-avatar"><?= $initials ?></div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= $nama ?></div>
            <div class="sidebar-user-role">Kepala BEM</div>
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
        <div class="page-header">
            <div>
                <h1 class="page-title">Selamat Datang, <?= $nama ?>!</h1>
                <p class="page-subtitle">Pantau ringkasan inventaris dan aktivitas peminjaman sebagai Kepala BEM.</p>
            </div>
        </div>

        <div class="stats-row" id="statsRow">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div><div class="stat-value" id="statTotal">—</div><div class="stat-label">Total Barang</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-check-circle"></i></div>
                <div><div class="stat-value" id="statTersedia">—</div><div class="stat-label">Stok Tersedia</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fa-solid fa-hand-holding-hand"></i></div>
                <div><div class="stat-value" id="statDipinjam">—</div><div class="stat-label">Sedang Dipinjam</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
                <div><div class="stat-value" id="statHabis">—</div><div class="stat-label">Stok Habis</div></div>
            </div>
        </div>

        <!-- Quick access cards -->
        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;margin-top:1rem">Akses Cepat</h2>
        <div class="stats-row">
            <a href="laporan.php" class="stat-card" style="cursor:pointer;text-decoration:none">
                <div class="stat-icon amber"><i class="fa-solid fa-chart-pie"></i></div>
                <div><div style="font-weight:600;font-size:.9rem">Laporan</div><div class="stat-label">Lihat laporan inventaris</div></div>
            </a>
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
    // Load stats
    fetch('../admin/api_barang.php?action=stats')
        .then(r=>r.json())
        .then(d=>{
            if(d.success){
                anim(document.getElementById('statTotal'),d.total);
                anim(document.getElementById('statTersedia'),d.tersedia);
                anim(document.getElementById('statDipinjam'),d.dipinjam);
                anim(document.getElementById('statHabis'),d.habis);
            }
        });
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
})();
</script>
</body>
</html>
