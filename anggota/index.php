<?php
session_start();

// Cek apakah user sudah login dan rolenya anggota
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'anggota') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Inventaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { 
            padding-top: 80px; 
            background-color: #f8fafc; /* Very light slate for premium clean look */
            font-family: 'Inter', sans-serif;
        }
        
        .dashboard-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 2rem; 
        }

        /* Hero / Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 3rem;
            border-radius: 24px;
            margin-bottom: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.2), 0 10px 10px -5px rgba(16, 185, 129, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background elements for welcome card */
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: 10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }

        .welcome-text {
            position: relative;
            z-index: 1;
        }

        .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .welcome-text p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            line-height: 1.6;
        }

        .action-btns {
            position: relative;
            z-index: 1;
        }

        .action-btns .btn {
            background: white;
            color: var(--primary-dark);
            font-weight: 700;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .action-btns .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .icon-blue { background: #eff6ff; color: #3b82f6; }
        .icon-green { background: #f0fdf4; color: #10b981; }
        .icon-yellow { background: #fefce8; color: #eab308; }
        .icon-red { background: #fef2f2; color: #ef4444; }

        .stat-info h3 {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-info .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        /* Modern Table Card */
        .card { 
            background: white; 
            border-radius: 24px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-responsive {
            overflow-x: auto;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 1rem 2rem;
            text-align: left;
            font-weight: 600;
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            padding: 1.25rem 2rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        tr {
            transition: background-color 0.2s;
        }

        tr:hover {
            background: #f8fafc;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Status Badges - Premium Look */
        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-pending { background: #fefce8; color: #a16207; border: 1px solid #fde047; }
        .status-disetujui { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .status-ditolak { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .btn-outline {
            border-color: #e2e8f0;
            color: #475569;
            background: white;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        /* Item list in table */
        .item-list {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .item-name {
            font-weight: 600;
            color: #0f172a;
        }
        
        .item-meta {
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            transform: scale(0.95);
            transition: transform 0.3s ease;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal.active .modal-content {
            transform: scale(1);
        }

        .modal-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: rotate(90deg);
        }

        .detail-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }

        .detail-info div strong {
            display: block;
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .detail-info div span {
            font-weight: 600;
            color: #0f172a;
            font-size: 1.1rem;
        }

        .alasan-tolak {
            background: #fef2f2;
            color: #991b1b;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 4px solid #ef4444;
            font-size: 0.95rem;
        }

        .item-status-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <nav class="navbar scrolled">
        <div class="nav-container">
            <div class="nav-logo">
                <div class="logo-circle" style="background-color: var(--primary);"><i class="fa-solid fa-user"></i></div>
                <span>Panel Anggota</span>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link active">Dashboard</a>
                <a href="katalog.php" class="nav-link">Katalog Barang</a>
                <a href="../logout.php" class="nav-link btn-contact"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-card">
            <div class="welcome-text">
                <h1>Halo, <?= htmlspecialchars(explode(' ', trim($_SESSION['nama_lengkap']))[0]); ?>! 👋</h1>
                <p>Selamat datang di Dashboard Anggota BEM. Pantau status peminjaman Anda, lihat riwayat, atau mulai ajukan peminjaman barang baru dengan mudah.</p>
            </div>
            <div class="action-btns">
                <a href="katalog.php" class="btn"><i class="fa-solid fa-layer-group" style="margin-right: 0.5rem;"></i> Eksplor Katalog</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue">
                    <i class="fa-solid fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Pengajuan</h3>
                    <div class="stat-value" id="statTotal">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-yellow">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Menunggu Persetujuan</h3>
                    <div class="stat-value" id="statPending">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-green">
                    <i class="fa-solid fa-hand-holding-hand"></i>
                </div>
                <div class="stat-info">
                    <h3>Sedang Dipinjam</h3>
                    <div class="stat-value" id="statAktif">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-red">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="stat-info">
                    <h3>Terlambat</h3>
                    <div class="stat-value" id="statTerlambat">0</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Riwayat Peminjaman</h2>
                <button class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="loadRiwayat()"><i class="fa-solid fa-rotate-right"></i> Segarkan</button>
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

    <!-- Modal Detail Peminjaman -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-close" onclick="closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </div>
            
            <div class="detail-header">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a;">Detail Peminjaman</h2>
                <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
                    <span class="text-muted" id="detIdPeminjaman" style="font-family: monospace; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px;"></span>
                </div>
            </div>

            <div id="alasanTolakContainer" style="display: none;"></div>

            <div class="detail-info">
                <div>
                    <strong>Waktu Pengajuan</strong>
                    <span id="detTglPengajuan"></span>
                </div>
                <div>
                    <strong>Status Persetujuan</strong>
                    <div id="detStatusApproval" style="margin-top: 0.25rem;"></div>
                </div>
            </div>

            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; color: #334155;">Daftar Barang</h3>
            <div class="table-responsive" style="margin-bottom: 1rem; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            loadRiwayat();
        });

        function getStatusBadge(status) {
            if (status === 'pending') return '<span class="status-badge status-pending"><i class="fa-solid fa-clock"></i> Pending</span>';
            if (status === 'disetujui') return '<span class="status-badge status-disetujui"><i class="fa-solid fa-check-circle"></i> Disetujui</span>';
            if (status === 'ditolak') return '<span class="status-badge status-ditolak"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>';
            return status;
        }

        async function loadStats() {
            try {
                const res = await fetch('api_anggota.php?action=stats');
                const data = await res.json();
                if (data.success) {
                    // Animate numbers
                    animateValue('statTotal', 0, data.total, 1000);
                    animateValue('statPending', 0, data.pending, 1000);
                    animateValue('statAktif', 0, data.aktif, 1000);
                    animateValue('statTerlambat', 0, data.terlambat, 1000);
                }
            } catch (err) {
                console.error('Failed to load stats', err);
            }
        }

        function animateValue(id, start, end, duration) {
            if (start === end) {
                document.getElementById(id).innerHTML = end;
                return;
            }
            let range = end - start;
            let current = start;
            let increment = end > start ? 1 : -1;
            let stepTime = Math.abs(Math.floor(duration / range));
            let obj = document.getElementById(id);
            let timer = setInterval(function() {
                current += increment;
                obj.innerHTML = current;
                if (current == end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        async function loadRiwayat() {
            const tbody = document.getElementById('riwayatBody');
            tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 3rem;"><i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color: var(--primary); margin-bottom: 1rem;"></i><br>Memuat data...</td></tr>`;
            
            try {
                const res = await fetch('api_anggota.php?action=riwayat');
                const data = await res.json();
                
                if (data.success) {
                    if (data.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 4rem; color: #64748b;">
                            <i class="fa-solid fa-folder-open fa-3x" style="margin-bottom: 1rem; color: #cbd5e1;"></i><br>
                            Anda belum memiliki riwayat peminjaman.
                        </td></tr>`;
                        return;
                    }

                    tbody.innerHTML = data.data.map(p => {
                        const dateObj = new Date(p.tgl_pengajuan);
                        const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                        const timeStr = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                        return `
                        <tr>
                            <td><strong style="font-family: monospace; color: var(--primary-dark);">${p.id_peminjaman}</strong></td>
                            <td>
                                <div class="item-list">
                                    <span class="item-name">${dateStr}</span>
                                    <span class="item-meta">${timeStr}</span>
                                </div>
                            </td>
                            <td>
                                <div class="item-list">
                                    <span class="item-name" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${p.daftar_barang}">${p.daftar_barang}</span>
                                    <span class="item-meta">${p.jumlah_item} jenis barang</span>
                                </div>
                            </td>
                            <td>${getStatusBadge(p.status_approval)}</td>
                            <td style="text-align: right;">
                                <button class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;" onclick="viewDetail('${p.id_peminjaman}')">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    `}).join('');
                }
            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="5" style="color: #ef4444; text-align: center; padding: 2rem;">Gagal memuat data. Periksa koneksi Anda.</td></tr>`;
            }
        }

        async function viewDetail(id) {
            try {
                const res = await fetch(`api_anggota.php?action=detail_riwayat&id=${id}`);
                const data = await res.json();
                if (data.success) {
                    const p = data.peminjaman;
                    
                    document.getElementById('detIdPeminjaman').textContent = p.id_peminjaman;
                    
                    const dateObj = new Date(p.tgl_pengajuan);
                    document.getElementById('detTglPengajuan').innerHTML = `${dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })} <span style="color:#64748b; font-weight:normal;">pukul ${dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span>`;
                    
                    document.getElementById('detStatusApproval').innerHTML = getStatusBadge(p.status_approval);

                    const tolakCont = document.getElementById('alasanTolakContainer');
                    if (p.status_approval === 'ditolak' && p.alasan_tolak) {
                        tolakCont.style.display = 'block';
                        tolakCont.innerHTML = `<div class="alasan-tolak"><strong style="display:block; margin-bottom:0.25rem;">Alasan Penolakan:</strong>${p.alasan_tolak}</div>`;
                    } else {
                        tolakCont.style.display = 'none';
                    }

                    const tbody = document.getElementById('detItemsBody');
                    tbody.innerHTML = data.items.map(item => {
                        let statusHtml = '';
                        if (item.status_item === 'dipinjam') statusHtml = '<span class="item-status-badge" style="background:#eff6ff; color:#3b82f6;">Dipinjam</span>';
                        else if (item.status_item === 'terlambat') statusHtml = '<span class="item-status-badge" style="background:#fef2f2; color:#ef4444;">Terlambat</span>';
                        else if (item.status_item === 'dikembalikan') statusHtml = '<span class="item-status-badge" style="background:#f0fdf4; color:#10b981;">Dikembalikan</span>';

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
                } else {
                    alert('Gagal memuat detail: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            }
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
