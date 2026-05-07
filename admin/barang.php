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
    <title>Manajemen Barang — Admin Inventaris</title>
    <meta name="description" content="Kelola data inventaris barang BEM Politeknik Purbaya">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
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
        <a href="barang.php" class="sidebar-link active">
            <i class="fa-solid fa-box-archive"></i> Manajemen Barang
        </a>
        <a href="peminjaman.php" class="sidebar-link">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Peminjaman
        </a>
        <a href="pengembalian.php" class="sidebar-link">
            <i class="fa-solid fa-rotate-left"></i> Pengembalian
        </a>

        <div class="sidebar-section-label">Pengaturan</div>
        <a href="#" class="sidebar-link">
            <i class="fa-solid fa-users"></i> Kelola User
        </a>
        <a href="#" class="sidebar-link">
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
                <span class="current">Manajemen Barang</span>
            </div>
        </div>
        <div class="top-header-right">
            <button class="header-icon-btn" title="Notifikasi">
                <i class="fa-regular fa-bell"></i>
                <span class="notif-dot"></span>
            </button>
        </div>
    </header>

    <!-- Page -->
    <div class="page-container">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Manajemen Barang</h1>
                <p class="page-subtitle">Kelola seluruh data inventaris barang BEM</p>
            </div>
            <button class="btn btn-primary" id="btnAddBarang">
                <i class="fa-solid fa-plus"></i> Tambah Barang
            </button>
        </div>

        <!-- Stats Row -->
        <div class="stats-row" id="statsRow">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div>
                    <div class="stat-value" id="statTotal">—</div>
                    <div class="stat-label">Total Barang</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-check-circle"></i></div>
                <div>
                    <div class="stat-value" id="statTersedia">—</div>
                    <div class="stat-label">Stok Tersedia</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fa-solid fa-hand-holding-hand"></i></div>
                <div>
                    <div class="stat-value" id="statDipinjam">—</div>
                    <div class="stat-label">Sedang Dipinjam</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
                <div>
                    <div class="stat-value" id="statHabis">—</div>
                    <div class="stat-label">Stok Habis</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama atau kode barang…">
                </div>
                <select class="filter-select" id="filterKategori">
                    <option value="">Semua Kategori</option>
                    <option value="Barang Habis Pakai">Barang Habis Pakai</option>
                    <option value="Barang Tidak Habis Pakai">Barang Tidak Habis Pakai</option>
                </select>
            </div>
            <button class="btn btn-secondary" id="btnRefresh">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
            </button>
        </div>

        <!-- Data Table -->
        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table" id="barangTable">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok Total</th>
                            <th>Tersedia</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- populated by JS -->
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span id="tableInfo">Memuat data…</span>
                <div class="pagination" id="pagination"></div>
            </div>
        </div>

    </div><!-- /page-container -->
</div><!-- /main-content -->

<!-- ===== MODAL: Tambah/Edit Barang ===== -->
<div class="modal-overlay" id="modalBarang">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Tambah Barang</h2>
            <button class="modal-close" id="modalClose"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formBarang" novalidate>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group" id="grpIdBarang">
                        <label class="form-label">Kode Barang <span style="color:var(--error)">*</span></label>
                        <input type="text" class="form-input" id="inpIdBarang" placeholder="BRG-011">
                        <span class="form-error" id="errIdBarang">Kode barang wajib diisi</span>
                    </div>
                    <div class="form-group" id="grpNamaBarang">
                        <label class="form-label">Nama Barang <span style="color:var(--error)">*</span></label>
                        <input type="text" class="form-input" id="inpNamaBarang" placeholder="Nama barang">
                        <span class="form-error" id="errNamaBarang">Nama barang wajib diisi</span>
                    </div>
                    <div class="form-group" id="grpKategori">
                        <label class="form-label">Kategori <span style="color:var(--error)">*</span></label>
                        <select class="form-select" id="inpKategori">
                            <option value="">Pilih kategori</option>
                            <option value="Barang Habis Pakai">Barang Habis Pakai</option>
                            <option value="Barang Tidak Habis Pakai">Barang Tidak Habis Pakai</option>
                        </select>
                        <span class="form-error" id="errKategori">Kategori wajib dipilih</span>
                    </div>
                    <div class="form-group" id="grpLokasi">
                        <label class="form-label">Lokasi</label>
                        <input type="text" class="form-input" id="inpLokasi" placeholder="Ruang Sekretariat">
                    </div>
                    <div class="form-group" id="grpStokTotal">
                        <label class="form-label">Stok Total <span style="color:var(--error)">*</span></label>
                        <input type="number" class="form-input" id="inpStokTotal" min="0" value="0">
                        <span class="form-error" id="errStokTotal">Stok total minimal 0</span>
                    </div>
                    <div class="form-group" id="grpStokTersedia">
                        <label class="form-label">Stok Tersedia</label>
                        <input type="number" class="form-input" id="inpStokTersedia" min="0" value="0">
                        <span class="form-error" id="errStokTersedia">Tidak boleh melebihi stok total</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelForm">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitForm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL: Konfirmasi Hapus ===== -->
<div class="modal-overlay" id="modalDelete">
    <div class="modal" style="max-width:420px">
        <div class="confirm-body">
            <div class="confirm-icon danger"><i class="fa-solid fa-trash-can"></i></div>
            <h3>Hapus Barang?</h3>
            <p id="deleteMsg">Barang ini akan dihapus secara permanen.</p>
        </div>
        <div class="modal-footer" style="justify-content:center">
            <button class="btn btn-secondary" id="btnCancelDelete">Batal</button>
            <button class="btn btn-danger" id="btnConfirmDelete">
                <i class="fa-solid fa-trash-can"></i> Hapus
            </button>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<script src="barang.js"></script>
</body>
</html>
