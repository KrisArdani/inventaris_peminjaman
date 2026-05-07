<?php
session_start();

// Cek apakah user sudah login dan rolenya kepala
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala - Inventaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { padding-top: 100px; }
        .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .card { background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-top: 2rem; border-left: 4px solid var(--primary); }
    </style>
</head>
<body>
    <nav class="navbar scrolled">
        <div class="nav-container">
            <div class="nav-logo">
                <div class="logo-circle"><i class="fa-solid fa-crown"></i></div>
                <span>Panel Kepala</span>
            </div>
            <div class="nav-links">
                <a href="#" class="nav-link active">Dashboard</a>
                <a href="../logout.php" class="nav-link btn-contact"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h1>
        <p>Anda login sebagai <strong>Kepala BEM</strong>.</p>
        
        <div class="card">
            <h2>Laporan Peminjaman</h2>
            <p>Fitur untuk Kepala BEM biasanya mencakup laporan barang, persetujuan khusus, dan statistik peminjaman barang secara keseluruhan.</p>
        </div>
    </div>
</body>
</html>
