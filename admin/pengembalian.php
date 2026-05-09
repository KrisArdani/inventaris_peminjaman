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
    <title>Manajemen Pengembalian — Admin Inventaris</title>
    <meta name="description" content="Kelola pengembalian barang inventaris BEM Politeknik Purbaya">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="peminjaman.css"> <!-- We can reuse some classes from peminjaman.css -->
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
        <a href="index.php" class="sidebar-link">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        <a href="barang.php" class="sidebar-link">
            <i class="fa-solid fa-box-archive"></i> Manajemen Barang
        </a>
        <a href="peminjaman.php" class="sidebar-link">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Peminjaman
        </a>
        <a href="pengembalian.php" class="sidebar-link active">
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

    <!-- Top Header -->
    <header class="top-header">
        <div class="top-header-left">
            <button class="btn-sidebar-toggle" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="breadcrumb">
                <a href="index.php">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Manajemen Pengembalian</span>
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

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Manajemen Pengembalian</h1>
                <p class="page-subtitle">Pantau barang yang dipinjam dan proses pengembalian</p>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-people-carry-box"></i></div>
                <div>
                    <div class="stat-value" id="statAktif">—</div>
                    <div class="stat-label">Item Dipinjam</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <div class="stat-value" id="statTerlambat">—</div>
                    <div class="stat-label">Item Terlambat</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-check-double"></i></div>
                <div>
                    <div class="stat-value" id="statDikembalikan">—</div>
                    <div class="stat-label">Dikembalikan (Bulan Ini)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fa-solid fa-coins"></i></div>
                <div>
                    <div class="stat-value" id="statDenda" style="font-size:1.25rem;line-height:1.2;margin-top:0.25rem">—</div>
                    <div class="stat-label">Denda (Bulan Ini)</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama peminjam, ID, atau barang…">
                </div>
                <select class="filter-select" id="filterStatus">
                    <option value="belum">Belum Dikembalikan</option>
                    <option value="sudah">Riwayat Pengembalian</option>
                </select>
            </div>
            <button class="btn btn-secondary" id="btnRefresh">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
            </button>
        </div>

        <!-- Data Table -->
        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table" id="pengembalianTable">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th style="text-align:center">Qty</th>
                            <th>Batas Waktu</th>
                            <th>Status</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
            <div class="table-footer">
                <span id="tableInfo">Memuat data…</span>
                <div class="pagination" id="pagination"></div>
            </div>
        </div>

    </div>
</div>

<!-- ===== MODAL: Proses Pengembalian ===== -->
<div class="modal-overlay" id="modalProses">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fa-solid fa-rotate-left" style="color:var(--primary);margin-right:.5rem"></i> Proses Pengembalian</h2>
            <button class="modal-close" onclick="APP.closeProses()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formProses" novalidate>
            <input type="hidden" id="inpDetailId">
            <div class="modal-body">
                
                <div class="detail-info" style="margin-bottom:1.5rem;background:var(--surface-container-low);padding:1rem;border-radius:var(--radius-md)">
                    <div class="detail-field full">
                        <span class="detail-label">Peminjam</span>
                        <span class="detail-value" id="lblPeminjam"></span>
                    </div>
                    <div class="detail-field full">
                        <span class="detail-label">Barang</span>
                        <span class="detail-value" id="lblBarang"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Status Item</span>
                        <span class="detail-value" id="lblStatus"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Batas Waktu</span>
                        <span class="detail-value" id="lblTglKembali"></span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:1rem">
                    <label class="form-label">Kondisi Barang Saat Dikembalikan <span style="color:var(--error)">*</span></label>
                    <select class="form-select" id="inpKondisi" required>
                        <option value="Baik">Baik (Tidak ada kerusakan)</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                        <option value="Hilang">Hilang</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom:1rem">
                    <label class="form-label">Denda (Rp)</label>
                    <input type="number" class="form-input" id="inpDenda" min="0" value="0" placeholder="0">
                    <span class="form-error" style="color:var(--outline);display:block;margin-top:.25rem;font-size:0.7rem">Isi jika terlambat atau ada kerusakan. Kosongkan (0) jika tidak ada.</span>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="APP.closeProses()">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitProses">
                    <i class="fa-solid fa-check"></i> Selesaikan Pengembalian
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL: Detail Riwayat ===== -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fa-solid fa-receipt" style="color:var(--primary);margin-right:.5rem"></i> Bukti Pengembalian</h2>
            <button class="modal-close" onclick="APP.closeDetail()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailRiwayatContent">
            <!-- Populated via JS -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="APP.closeDetail()">Tutup</button>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<script src="pengembalian.js"></script>
</body>
</html>
