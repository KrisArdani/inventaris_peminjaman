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
    <title>Laporan — Kepala Inventaris</title>
    <meta name="description" content="Laporan data inventaris BEM Politeknik Purbaya">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/admin.css?v=5">
    <style>
        .report-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .report-card {
            background: var(--surface-container);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            cursor: pointer;
            transition: all .2s ease;
            border: 1px solid transparent;
        }
        .report-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .report-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--surface-container-high);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .report-card-info h3 {
            margin: 0 0 0.25rem 0;
            font-size: 1.1rem;
        }
        .report-card-info p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--outline);
            line-height: 1.4;
        }
        
        .report-view {
            display: none;
            margin-top: 1rem;
            animation: fadeIn .3s ease;
        }
        .report-view.active {
            display: block;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .btn-back {
            background: transparent;
            border: none;
            color: var(--on-surface);
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background .2s;
        }
        .btn-back:hover {
            background: var(--surface-container-high);
        }
        
        .filters {
            background: var(--surface-container);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-group label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--outline);
        }
        .filter-input {
            padding: 0.6rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--surface-container-highest);
            background: var(--surface-container-high);
            color: var(--on-surface);
            font-family: inherit;
        }
        .filter-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .export-actions {
            margin-left: auto;
            display: flex;
            gap: 0.5rem;
        }
        
        .report-table-wrapper {
            background: var(--surface-container);
            border-radius: 12px;
            overflow-x: auto;
            margin-top: 1rem;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--surface-container-highest);
            font-size: 0.9rem;
        }
        .table th {
            color: var(--outline);
            font-weight: 500;
            background: var(--surface-container-high);
            white-space: nowrap;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--outline);
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon sidebar-brand-icon--img"><img src="../assets/images/logo bem.png" alt="Logo BEM KM Politeknik Purbaya"></div>
        <span>Inventaris BEM</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        <a href="index.php" class="sidebar-link">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>

        <div class="sidebar-section-label">Laporan</div>
        <a href="laporan.php" class="sidebar-link active">
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
                <a href="index.php">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Laporan</span>
            </div>
        </div>
    </header>

    <div class="page-container">
        
        <!-- View: Cards -->
        <div id="viewCards">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Pusat Laporan</h1>
                    <p class="page-subtitle">Pilih jenis laporan yang ingin Anda lihat dan unduh</p>
                </div>
            </div>
            
            <div class="report-cards">
                <div class="report-card" onclick="openReport('barang')">
                    <div class="report-card-icon"><i class="fa-solid fa-box-archive"></i></div>
                    <div class="report-card-info">
                        <h3>Laporan Barang</h3>
                        <p>Daftar seluruh barang inventaris, kategori, stok, dan status ketersediaan.</p>
                    </div>
                </div>
                <div class="report-card" onclick="openReport('peminjaman')">
                    <div class="report-card-icon" style="color: #eab308; background: #fef08a15"><i class="fa-solid fa-arrow-right-arrow-left"></i></div>
                    <div class="report-card-info">
                        <h3>Laporan Peminjaman</h3>
                        <p>Riwayat pengajuan peminjaman, rincian kegiatan, dan status persetujuan.</p>
                    </div>
                </div>
                <div class="report-card" onclick="openReport('pengembalian')">
                    <div class="report-card-icon" style="color: #10b981; background: #a7f3d015"><i class="fa-solid fa-rotate-left"></i></div>
                    <div class="report-card-info">
                        <h3>Laporan Pengembalian</h3>
                        <p>Riwayat pengembalian barang, keterlambatan, denda, dan kondisi barang.</p>
                    </div>
                </div>
                <div class="report-card" onclick="openReport('anggota')">
                    <div class="report-card-icon" style="color: #8b5cf6; background: #ddd6fe15"><i class="fa-solid fa-users"></i></div>
                    <div class="report-card-info">
                        <h3>Laporan Anggota</h3>
                        <p>Data pengguna terdaftar, statistik peminjaman, dan denda per pengguna.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- View: Report Detail -->
        <div class="report-view" id="viewReport">
            <div class="report-header">
                <button class="btn-back" onclick="closeReport()">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Menu Laporan
                </button>
                <h2 id="reportTitle" style="margin:0;font-size:1.25rem;">Laporan</h2>
            </div>
            
            <div class="filters" id="filterArea">
                <!-- Filters injected via JS based on type -->
            </div>
            
            <div class="report-table-wrapper">
                <table class="table" id="reportTable">
                    <thead id="reportThead"></thead>
                    <tbody id="reportTbody"></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Libraries for Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script src="laporan.js"></script>
</body>
</html>
