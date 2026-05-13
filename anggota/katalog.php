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
    <title>Katalog Barang - Inventaris BEM</title>
    <!-- Using Manrope as requested in previous contexts, and Inter for body -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { 
            padding-top: 80px; 
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4 {
            font-family: 'Manrope', sans-serif;
        }
        
        .katalog-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            border-radius: 0 0 40px 40px;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.3);
        }

        .katalog-header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .katalog-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .katalog-header p {
            font-size: 1.15rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Controls */
        .controls-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            gap: 1.5rem;
            flex-wrap: wrap;
            background: white;
            padding: 1rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .search-bar {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .search-bar i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .filter-kategori {
            padding: 1rem 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            font-family: inherit;
            font-weight: 500;
            font-size: 1rem;
            min-width: 220px;
            color: #334155;
            cursor: pointer;
            outline: none;
            transition: all 0.3s;
        }

        .filter-kategori:focus {
            border-color: var(--primary);
            background: white;
        }

        /* Grid Barang */
        .barang-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .barang-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            cursor: pointer;
            position: relative;
        }

        .barang-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            border-color: #cbd5e1;
        }

        .barang-img {
            height: 220px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: #94a3b8;
            position: relative;
            overflow: hidden;
        }
        
        .barang-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .barang-card:hover .barang-img img {
            transform: scale(1.05);
        }

        .barang-card:hover .barang-img i {
            color: var(--primary);
        }

        .stok-badge-float {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #166534;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .stok-badge-float.habis {
            color: #991b1b;
            background: rgba(254, 226, 226, 0.9);
        }

        .barang-info {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .barang-kategori {
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .barang-nama {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #0f172a;
            line-height: 1.3;
        }

        /* Glassmorphism Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
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
            border-radius: 24px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal.active .modal-content {
            transform: scale(1);
        }

        .modal-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
        }

        .modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: rotate(90deg);
        }

        /* Detail Modal Specifics */
        .detail-layout {
            display: flex;
            flex-direction: column;
        }
        @media(min-width: 768px) {
            .detail-layout { flex-direction: row; }
        }

        .detail-img-area {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8rem;
            color: var(--primary);
            flex: 1;
            min-height: 300px;
            overflow: hidden;
            position: relative;
        }
        
        .detail-img-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0; left: 0;
        }

        .detail-info-area {
            padding: 3rem;
            flex: 1.5;
            display: flex;
            flex-direction: column;
        }

        .detail-info-area h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .detail-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.95rem;
        }

        .meta-item i { color: var(--primary); }

        .detail-desc {
            color: #475569;
            line-height: 1.7;
            margin-bottom: 2rem;
            flex-grow: 1;
        }

        .btn-pinjam-ini {
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
        }

        .btn-pinjam-ini:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }

        .btn-pinjam-ini:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        /* Form Modal Specifics */
        .form-modal-content {
            max-width: 800px;
            padding: 3rem;
        }

        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
        }

        .cart-items-list {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px dashed #cbd5e1;
        }

        .cart-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .cart-item-info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .qty-input {
            width: 70px;
            padding: 0.5rem;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-weight: 600;
            font-family: inherit;
        }

        .btn-remove {
            color: #ef4444;
            background: #fef2f2;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-remove:hover {
            background: #fee2e2;
            transform: scale(1.05);
        }

        .btn-tambah-lain {
            background: transparent;
            color: var(--primary);
            border: 2px dashed var(--primary);
            padding: 1rem;
            border-radius: 12px;
            width: 100%;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-tambah-lain:hover {
            background: #f0fdf4;
        }

        .form-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: #334155;
            font-size: 0.95rem;
        }

        .form-group input[type="date"] {
            padding: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            color: #0f172a;
            transition: border-color 0.2s;
        }

        .form-group input[type="date"]:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Cart Floating Button */
        .cart-float {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--primary-dark);
            color: white;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.3);
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
        }

        .cart-float:hover {
            transform: scale(1.1) translateY(-5px);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            font-size: 0.85rem;
            font-weight: 800;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
        }

    </style>
</head>
<body>
    <nav class="navbar scrolled">
        <div class="nav-container">
            <div class="nav-logo">
                <div class="logo-circle logo-circle--img" style="width:40px;height:40px;">
                    <img src="../assets/images/logo bem.png" alt="Logo BEM KM Politeknik Purbaya" class="nav-logo-img">
                </div>
                <span>BEM Purbaya</span>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Dashboard</a>
                <a href="katalog.php" class="nav-link active">Katalog Barang</a>
                <a href="../logout.php" class="nav-link btn-contact"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </nav>

    <header class="katalog-header">
        <h1>Katalog Inventaris</h1>
        <p>Sistem Pendataan & Informasi Barang Inventaris Politeknik Purbaya.</p>
    </header>

    <div class="container">
        <div class="controls-wrapper">
            <div class="search-bar">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari nama atau kode barang...">
            </div>
            <select id="kategoriFilter" class="filter-kategori">
                <option value="">Semua Kategori</option>
                <option value="Barang Habis Pakai">Barang Habis Pakai</option>
                <option value="Barang Tidak Habis Pakai">Barang Tidak Habis Pakai</option>
            </select>
        </div>

        <div class="barang-grid" id="barangGrid">
            <!-- Loading state -->
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: #94a3b8;">
                <i class="fa-solid fa-circle-notch fa-spin fa-3x"></i>
                <p style="margin-top: 1.5rem; font-weight: 500;">Memuat katalog barang...</p>
            </div>
        </div>
    </div>

    <!-- Floating Cart Button -->
    <a href="form_peminjaman.php" class="cart-float" id="cartBtn" style="display: none; text-decoration: none;">
        <i class="fa-solid fa-clipboard-list"></i>
        <div class="cart-badge" id="cartBadge">0</div>
    </a>

    <!-- Modal Detail Barang -->
    <div class="modal" id="detailModal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-close" onclick="closeDetailModal()"><i class="fa-solid fa-xmark"></i></div>
            
            <div class="detail-layout" id="detailContent">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>



    <script>
        let allBarang = [];
        let cart = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadCart();
            loadBarang();
            document.getElementById('searchInput').addEventListener('input', renderGrid);
            document.getElementById('kategoriFilter').addEventListener('change', renderGrid);
        });

        function loadCart() {
            const c = localStorage.getItem('cart_peminjaman');
            if (c) {
                cart = JSON.parse(c);
            }
            updateCartUI();
        }

        function saveCart() {
            localStorage.setItem('cart_peminjaman', JSON.stringify(cart));
        }

        async function loadBarang() {
            try {
                const res = await fetch('api_anggota.php?action=katalog');
                const data = await res.json();
                if (data.success) {
                    allBarang = data.data;
                    renderGrid();
                }
            } catch (err) {
                console.error(err);
            }
        }

        function getIconForKategori(kat) {
            const k = (kat || '').toLowerCase();
            if (k.includes('tidak habis')) return 'fa-box-archive';
            if (k.includes('habis')) return 'fa-box-open';
            return 'fa-box';
        }

        function renderGrid() {
            const grid = document.getElementById('barangGrid');
            const search = document.getElementById('searchInput').value.toLowerCase();
            const filter = document.getElementById('kategoriFilter').value;

            grid.innerHTML = '';

            const filtered = allBarang.filter(b => {
                const matchSearch = b.nama_barang.toLowerCase().includes(search) || b.id_barang.toLowerCase().includes(search);
                const matchKat = filter === '' || b.kategori === filter;
                return matchSearch && matchKat;
            });

            if (filtered.length === 0) {
                grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: #94a3b8;">
                    <i class="fa-solid fa-search fa-3x" style="margin-bottom: 1.5rem; color: #cbd5e1;"></i>
                    <p style="font-size: 1.1rem;">Tidak ada barang yang sesuai dengan pencarian Anda.</p>
                </div>`;
                return;
            }

            filtered.forEach(b => {
                const isHabis = parseInt(b.stok_tersedia) === 0;
                const inCart = cart.find(item => item.id_barang === b.id_barang);
                
                const imgHtml = b.gambar ? `<img src="${b.gambar}" alt="${b.nama_barang}">` : `<i class="fa-solid ${getIconForKategori(b.kategori)}"></i>`;
                
                const card = `
                    <div class="barang-card">
                        <div class="barang-img" onclick="openDetailModal('${b.id_barang}')">
                            ${imgHtml}
                            <div class="stok-badge-float ${isHabis ? 'habis' : ''}">
                                ${isHabis ? 'Habis' : 'Sisa: ' + b.stok_tersedia}
                            </div>
                            ${inCart ? '<div style="position:absolute; top:1rem; left:1rem; background:var(--primary); color:white; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.2);"><i class="fa-solid fa-check"></i></div>' : ''}
                        </div>
                        <div class="barang-info" onclick="openDetailModal('${b.id_barang}')">
                            <span class="barang-kategori">${b.kategori}</span>
                            <h3 class="barang-nama">${b.nama_barang}</h3>
                            <p style="font-size: 0.85rem; color: #64748b; margin-top: auto; margin-bottom: 1rem;">
                                <i class="fa-solid fa-location-dot" style="margin-right:0.3rem;"></i> ${b.lokasi}
                            </p>
                        </div>
                        <div style="padding: 0 1.5rem 1.5rem;">
                            ${isHabis ? 
                                '<button class="btn-pinjam-ini" disabled style="padding: 0.75rem; font-size: 0.95rem;">Stok Habis</button>' : 
                                (inCart ? 
                                    '<button class="btn-pinjam-ini" style="background:#0f172a; padding: 0.75rem; font-size: 0.95rem;" onclick="window.location.href=\'form_peminjaman.php\'">Sudah Dipilih, Lanjut Form</button>' : 
                                    `<button class="btn-pinjam-ini" style="padding: 0.75rem; font-size: 0.95rem;" onclick="addToCartAndGoToForm('${b.id_barang}')"><i class="fa-solid fa-plus"></i> Pinjam</button>`
                                )
                            }
                        </div>
                    </div>
                `;
                grid.insertAdjacentHTML('beforeend', card);
            });
        }

        function openDetailModal(id) {
            const b = allBarang.find(item => item.id_barang === id);
            if (!b) return;

            const isHabis = parseInt(b.stok_tersedia) === 0;
            const inCart = cart.find(item => item.id_barang === b.id_barang);
            
            let btnAction = '';
            if (isHabis) {
                btnAction = `<button class="btn-pinjam-ini" disabled><i class="fa-solid fa-xmark"></i> Stok Tidak Tersedia</button>`;
            } else if (inCart) {
                btnAction = `<button class="btn-pinjam-ini" style="background:#0f172a;" onclick="window.location.href='form_peminjaman.php'"><i class="fa-solid fa-arrow-right"></i> Lanjut ke Form Peminjaman</button>`;
            } else {
                btnAction = `<button class="btn-pinjam-ini" onclick="addToCartAndGoToForm('${b.id_barang}')"><i class="fa-solid fa-plus"></i> Tambahkan & Pinjam</button>`;
            }

            // Using placeholder for deskripsi if not fetched (api_anggota doesn't fetch deskripsi currently, let's just show basic info)
            // Note: I will update api_anggota.php later to include deskripsi if needed, or just show a default string.
            const deskripsi = b.deskripsi || "Barang inventaris BEM Politeknik Purbaya.";
            const imgHtml = b.gambar ? `<img src="${b.gambar}" alt="${b.nama_barang}">` : `<i class="fa-solid ${getIconForKategori(b.kategori)}"></i>`;

            const content = `
                <div class="detail-img-area">
                    ${imgHtml}
                </div>
                <div class="detail-info-area">
                    <span style="color:var(--primary); font-weight:700; font-size:0.9rem; letter-spacing:0.05em; text-transform:uppercase; margin-bottom:0.5rem; display:block;">${b.kategori}</span>
                    <h2>${b.nama_barang}</h2>
                    
                    <div class="detail-meta">
                        <div class="meta-item">
                            <i class="fa-solid fa-barcode"></i>
                            <span>${b.id_barang}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>${b.lokasi}</span>
                        </div>
                        <div class="meta-item" style="color: ${isHabis ? '#ef4444' : '#10b981'}; font-weight:600;">
                            <i class="fa-solid fa-box"></i>
                            <span>Sisa Stok: ${b.stok_tersedia}</span>
                        </div>
                    </div>

                    <div class="detail-desc">
                        ${deskripsi}
                    </div>

                    <div style="margin-top: auto;">
                        ${btnAction}
                    </div>
                </div>
            `;

            document.getElementById('detailContent').innerHTML = content;
            document.getElementById('detailModal').classList.add('active');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        function addToCartAndGoToForm(id) {
            const barang = allBarang.find(b => b.id_barang === id);
            if (barang && !cart.find(i => i.id_barang === id)) {
                cart.push({
                    ...barang,
                    jumlah_pinjam: 1
                });
                saveCart();
                updateCartUI();
                renderGrid();
            }
            window.location.href = 'form_peminjaman.php';
        }

        function updateCartUI() {
            const badge = document.getElementById('cartBadge');
            const floatBtn = document.getElementById('cartBtn');
            if (cart.length > 0) {
                floatBtn.style.display = 'flex';
                badge.textContent = cart.length;
            } else {
                floatBtn.style.display = 'none';
            }
        }

        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target == document.getElementById('detailModal')) closeDetailModal();
        }
    </script>
</body>
</html>
