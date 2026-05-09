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
    <title>Kelola User — Admin Inventaris</title>
    <meta name="description" content="Kelola akun pengguna aplikasi inventaris BEM">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .role-admin { background: #fee2e2; color: #b91c1c; }
        .role-anggota { background: #dbeafe; color: #1d4ed8; }
        .role-kepala { background: #fef3c7; color: #b45309; }
        
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .user-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.9rem;
        }
        .user-details { display: flex; flex-direction: column; }
        .user-name { font-weight: 600; color: var(--on-surface); }
        .user-username { font-size: 0.8rem; color: var(--outline); }
    </style>
</head>
<body>

<!-- Sidebar Overlay -->
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
        <a href="pengembalian.php" class="sidebar-link">
            <i class="fa-solid fa-rotate-left"></i> Pengembalian
        </a>

        <div class="sidebar-section-label">Pengaturan</div>
        <a href="user.php" class="sidebar-link active">
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
                <a href="index.php">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Kelola User</span>
            </div>
        </div>
    </header>

    <div class="page-container">
        
        <div class="page-header">
            <div>
                <h1 class="page-title">Kelola Pengguna</h1>
                <p class="page-subtitle">Manajemen akun admin, anggota, dan kepala biro.</p>
            </div>
            <button class="btn btn-primary" id="btnAddUser">
                <i class="fa-solid fa-user-plus"></i> Tambah User
            </button>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Total Pengguna</p>
                    <h3 class="stat-value" id="statTotal">-</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8"><i class="fa-solid fa-user-tag"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Total Anggota</p>
                    <h3 class="stat-value" id="statAnggota">-</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#b91c1c"><i class="fa-solid fa-user-shield"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Total Admin</p>
                    <h3 class="stat-value" id="statAdmin">-</h3>
                </div>
            </div>
        </div>

        <!-- Table Controls -->
        <div class="table-controls">
            <div class="table-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Cari nama, username, atau ormawa...">
            </div>
            <div class="table-filters">
                <select class="table-select" id="roleFilter">
                    <option value="semua">Semua Role</option>
                    <option value="anggota">Anggota</option>
                    <option value="admin">Admin</option>
                    <option value="kepala">Kepala</option>
                </select>
                <button class="btn-icon" id="btnRefresh" title="Refresh Data"><i class="fa-solid fa-rotate-right"></i></button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>PENGGUNA</th>
                        <th>ROLE</th>
                        <th>NO. HP</th>
                        <th>ASAL ORMAWA</th>
                        <th>TANGGAL DAFTAR</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Data diload via JS -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination" id="pagination"></div>

    </div>
</div>

<!-- Modal Form -->
<div class="modal-overlay" id="modalUser">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah User</h3>
            <button class="modal-close" id="modalClose"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formUser" novalidate>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full" id="grpNama">
                        <label class="form-label">Nama Lengkap <span style="color:var(--error)">*</span></label>
                        <input type="text" class="form-input" id="inpNama" required>
                        <span class="form-error">Nama wajib diisi</span>
                    </div>
                    <div class="form-group" id="grpUsername">
                        <label class="form-label">Username <span style="color:var(--error)">*</span></label>
                        <input type="text" class="form-input" id="inpUsername" required>
                        <span class="form-error">Username wajib diisi</span>
                    </div>
                    <div class="form-group" id="grpRole">
                        <label class="form-label">Role <span style="color:var(--error)">*</span></label>
                        <select class="form-input" id="inpRole" required>
                            <option value="anggota">Anggota</option>
                            <option value="admin">Admin</option>
                            <option value="kepala">Kepala</option>
                        </select>
                    </div>
                    <div class="form-group" id="grpPassword">
                        <label class="form-label">Password <span id="passReq" style="color:var(--error)">*</span></label>
                        <input type="password" class="form-input" id="inpPassword" placeholder="Biarkan kosong jika tidak ingin diubah (Edit)">
                        <span class="form-error">Password wajib diisi</span>
                    </div>
                    <div class="form-group" id="grpNoHp">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-input" id="inpNoHp">
                    </div>
                    <div class="form-group full" id="grpOrmawa">
                        <label class="form-label">Asal Ormawa</label>
                        <input type="text" class="form-input" id="inpOrmawa" placeholder="Hanya untuk role Anggota">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" id="btnCancelForm">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitForm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal-overlay" id="modalDelete">
    <div class="modal" style="max-width: 400px; text-align: center;">
        <div class="modal-body" style="padding: 2rem;">
            <div style="font-size: 3rem; color: var(--error); margin-bottom: 1rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="margin-bottom: 0.5rem;">Hapus Pengguna?</h3>
            <p style="color: var(--outline); margin-bottom: 1.5rem;" id="deleteMsg">Anda yakin ingin menghapus pengguna ini?</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button class="btn" id="btnCancelDelete">Batal</button>
                <button class="btn" style="background: var(--error); color: white; border: none;" id="btnConfirmDelete">
                    <i class="fa-solid fa-trash-can"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script src="user.js"></script>
</body>
</html>
