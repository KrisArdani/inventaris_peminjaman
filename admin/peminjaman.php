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
    <title>Manajemen Peminjaman — Admin Inventaris</title>
    <meta name="description" content="Kelola peminjaman barang inventaris BEM Politeknik Purbaya">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="peminjaman.css">
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
        <a href="index.php" class="sidebar-link">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        <a href="barang.php" class="sidebar-link">
            <i class="fa-solid fa-box-archive"></i> Manajemen Barang
        </a>
        <a href="peminjaman.php" class="sidebar-link active">
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

    <!-- Top Header -->
    <header class="top-header">
        <div class="top-header-left">
            <button class="btn-sidebar-toggle" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
            <div class="breadcrumb">
                <a href="index.php">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Manajemen Peminjaman</span>
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
                <h1 class="page-title">Manajemen Peminjaman</h1>
                <p class="page-subtitle">Kelola pengajuan dan status peminjaman barang</p>
            </div>
            <button class="btn btn-primary" id="btnCreatePeminjaman">
                <i class="fa-solid fa-plus"></i> Buat Peminjaman
            </button>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-value" id="statTotal">—</div>
                    <div class="stat-label">Total Peminjaman</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-value" id="statPending">—</div>
                    <div class="stat-label">Menunggu Persetujuan</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="stat-value" id="statApproved">—</div>
                    <div class="stat-label">Disetujui</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <div class="stat-value" id="statTerlambat">—</div>
                    <div class="stat-label">Terlambat</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama peminjam atau ID…">
                </div>
                <select class="filter-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
            <button class="btn btn-secondary" id="btnRefresh">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
            </button>
        </div>

        <!-- Data Table -->
        <div class="table-card">
            <div class="table-wrapper">

                <table class="data-table" id="peminjamanTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Peminjam</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Jumlah Item</th>
                            <th>Barang</th>
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

<!-- ===== MODAL: Detail Peminjaman ===== -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fa-solid fa-file-lines" style="color:var(--primary);margin-right:.5rem"></i> Detail Peminjaman</h2>
            <button class="modal-close" onclick="APP.closeDetail()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div style="text-align:center;padding:2rem;color:var(--outline)"><i class="fa-solid fa-spinner fa-spin"></i> Memuat…</div>
        </div>
        <div class="modal-footer" id="detailFooter"></div>
    </div>
</div>

<!-- ===== MODAL: Tolak Peminjaman ===== -->
<div class="modal-overlay" id="modalReject">
    <div class="modal" style="max-width:440px">
        <div class="modal-header">
            <h2 class="modal-title">Tolak Peminjaman</h2>
            <button class="modal-close" onclick="APP.closeReject()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Alasan Penolakan</label>
                <textarea class="form-textarea" id="inpAlasanTolak" rows="3" placeholder="Tuliskan alasan penolakan…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="APP.closeReject()">Batal</button>
            <button class="btn btn-danger" id="btnConfirmReject">
                <i class="fa-solid fa-xmark"></i> Tolak Peminjaman
            </button>
        </div>
    </div>
</div>

<!-- ===== MODAL: Buat Peminjaman ===== -->
<div class="modal-overlay" id="modalCreate">
    <div class="modal" style="max-width:620px">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fa-solid fa-plus-circle" style="color:var(--primary);margin-right:.5rem"></i> Buat Peminjaman Baru</h2>
            <button class="modal-close" onclick="APP.closeCreate()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formCreate" novalidate>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full" id="grpUser">
                        <label class="form-label">Peminjam (Anggota) <span style="color:var(--error)">*</span></label>
                        <select class="form-select" id="inpUser"><option value="">Pilih anggota…</option></select>
                        <span class="form-error">Pilih peminjam</span>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nama Kegiatan / Acara</label>
                        <input type="text" class="form-input" id="inpKegiatan" placeholder="Contoh: Rapat Kerja BEM 2026">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Tujuan / Keperluan</label>
                        <textarea class="form-input" id="inpTujuan" rows="2" placeholder="Jelaskan keperluan peminjaman"></textarea>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Lokasi Penggunaan</label>
                        <input type="text" class="form-input" id="inpLokasi" placeholder="Contoh: Gedung A Ruang 101">
                    </div>
                    <div class="form-group" id="grpTglPinjam">
                        <label class="form-label">Tanggal Pinjam <span style="color:var(--error)">*</span></label>
                        <input type="date" class="form-input" id="inpTglPinjam">
                        <span class="form-error">Tanggal wajib diisi</span>
                    </div>
                    <div class="form-group" id="grpTglKembali">
                        <label class="form-label">Tanggal Kembali <span style="color:var(--error)">*</span></label>
                        <input type="date" class="form-input" id="inpTglKembali">
                        <span class="form-error">Tanggal wajib diisi</span>
                    </div>
                </div>

                <div style="margin-top:1.25rem">
                    <label class="form-label" style="margin-bottom:.5rem;display:block">Daftar Barang <span style="color:var(--error)">*</span></label>
                    <div id="itemRows"></div>
                    <button type="button" class="btn btn-secondary btn-sm" id="btnAddItem" style="margin-top:.5rem">
                        <i class="fa-solid fa-plus"></i> Tambah Barang
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="APP.closeCreate()">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitCreate">
                    <i class="fa-solid fa-paper-plane"></i> Buat Peminjaman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL: Konfirmasi Approve ===== -->
<div class="modal-overlay" id="modalApprove">
    <div class="modal" style="max-width:420px">
        <div class="confirm-body">
            <div class="confirm-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="fa-solid fa-circle-check"></i></div>
            <h3>Setujui Peminjaman?</h3>
            <p id="approveMsg">Stok barang akan dikurangi sesuai jumlah peminjaman.</p>
        </div>
        <div class="modal-footer" style="justify-content:center">
            <button class="btn btn-secondary" onclick="APP.closeApprove()">Batal</button>
            <button class="btn btn-primary" id="btnConfirmApprove">
                <i class="fa-solid fa-check"></i> Setujui
            </button>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<script src="peminjaman.js"></script>
</body>
</html>
