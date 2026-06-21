<?php
session_start();

// Cek apakah user sudah login dan rolenya anggota
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'anggota') {
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
    <title>Katalog Barang - Inventaris BEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="anggota.css?v=5.0">
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
            <a href="katalog.php" class="sidebar-link active">
                <i class="fa-solid fa-layer-group"></i> Katalog Inventaris
            </a>
            <a href="form_peminjaman.php" class="sidebar-link">
                <i class="fa-solid fa-file-signature"></i> Form Pengajuan
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-avatar"><?= $initials ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= $nama ?></div>
                <div class="sidebar-user-role">Anggota BEM</div>
            </div>
            <a href="../logout.php" class="btn-logout" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="top-header-left">
                <button class="btn-sidebar-toggle" id="btnToggleSidebar"><i class="fa-solid fa-bars"></i></button>
                <div class="breadcrumb">
                    <a href="index.php">Dashboard</a>
                    <span class="sep">/</span>
                    <span class="current">Katalog Barang</span>
                </div>
            </div>
            <div class="top-header-right">
                <a href="form_peminjaman.php" class="cart-indicator-btn" id="headerCartBtn" title="Lihat Pengajuan">
                    <i class="fa-solid fa-file-signature"></i>
                    <div class="cart-badge" id="headerCartBadge" style="display: none;">0</div>
                </a>
            </div>
        </header>

        <!-- Page Container -->
        <div class="page-container">
            <!-- Catalog Header -->
            <div class="katalog-header">
                <div class="katalog-header-mesh"></div>
                <div class="katalog-header-dots"></div>
                <div class="katalog-header-content">
                    <div class="katalog-badge">
                        <i class="fa-solid fa-layer-group"></i> BEM KM Politeknik Purbaya
                    </div>
                    <h1>Katalog Inventaris</h1>
                    <p>Sistem Pendataan & Informasi Barang Inventaris Politeknik Purbaya.</p>
                </div>
            </div>

            <!-- Controls -->
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

            <!-- Grid Barang -->
            <div class="barang-grid" id="barangGrid">
                <!-- Loading state -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: #94a3b8;">
                    <i class="fa-solid fa-circle-notch fa-spin fa-3x" style="color: var(--primary);"></i>
                    <p style="margin-top: 1.5rem; font-weight: 500;">Memuat katalog barang...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Cart Button (Optional fallback for mobile) -->
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

            // Sidebar toggle
            const btnToggle = document.getElementById('btnToggleSidebar');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (btnToggle) {
                btnToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('open');
                    overlay.classList.toggle('active');
                });
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                });
            }
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
                
                const imgHtml = b.gambar ? `<img src="${b.gambar}" alt="${b.nama_barang}">` : `<div class="barang-img-placeholder"><i class="fa-solid ${getIconForKategori(b.kategori)}"></i></div>`;
                
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
                            <p class="barang-lokasi" style="margin-top: auto;">
                                <i class="fa-solid fa-location-dot"></i> ${b.lokasi}
                            </p>
                        </div>
                        <div style="padding: 0 1.25rem 1.25rem;">
                            ${isHabis ? 
                                '<button class="btn btn-outline" disabled style="width: 100%;">Stok Habis</button>' : 
                                (inCart ? 
                                    '<button class="btn btn-outline" style="background:#f1f5f9; color: var(--on-surface); width: 100%;" onclick="window.location.href=\'form_peminjaman.php\'"><i class="fa-solid fa-check"></i> Sudah Dipilih</button>' : 
                                    `<button class="btn btn-primary" style="width: 100%;" onclick="addToCartAndGoToForm('${b.id_barang}')"><i class="fa-solid fa-plus"></i> Pilih Barang</button>`
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
                btnAction = `<button class="btn btn-outline" disabled style="width:100%; padding: 0.85rem;"><i class="fa-solid fa-xmark"></i> Stok Tidak Tersedia</button>`;
            } else if (inCart) {
                btnAction = `<button class="btn btn-outline" style="background:#f1f5f9; width:100%; padding: 0.85rem;" onclick="window.location.href='form_peminjaman.php'"><i class="fa-solid fa-arrow-right"></i> Lanjut ke Form Pengajuan</button>`;
            } else {
                btnAction = `<button class="btn btn-primary" style="width:100%; padding: 0.85rem;" onclick="addToCartAndGoToForm('${b.id_barang}')"><i class="fa-solid fa-plus"></i> Pilih & Ajukan</button>`;
            }

            const deskripsi = b.deskripsi || "Barang inventaris resmi BEM Politeknik Purbaya. Pastikan barang dipergunakan dengan bertanggung jawab sesuai dengan keperluan yang diajukan.";
            const imgHtml = b.gambar ? `<img src="${b.gambar}" alt="${b.nama_barang}">` : `<div class="barang-img-placeholder" style="font-size: 6rem; height: 100%; display: grid; place-items: center;"><i class="fa-solid ${getIconForKategori(b.kategori)}"></i></div>`;

            const content = `
                <div class="detail-img-area">
                    ${imgHtml}
                </div>
                <div class="detail-info-area">
                    <span class="barang-kategori" style="font-size: 0.8rem; margin-bottom: 0.5rem; display: block;">${b.kategori}</span>
                    <h2 style="font-size: 1.75rem; margin-bottom: 1rem; line-height: 1.3;">${b.nama_barang}</h2>
                    
                    <div class="detail-meta">
                        <div class="meta-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>${b.lokasi}</span>
                        </div>
                        <div class="meta-item" style="color: ${isHabis ? '#ef4444' : '#166534'}; font-weight:700;">
                            <i class="fa-solid fa-box"></i>
                            <span>Sisa Stok: ${b.stok_tersedia}</span>
                        </div>
                    </div>

                    <div class="detail-desc" style="font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
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
                    id_barang: barang.id_barang,
                    nama_barang: barang.nama_barang,
                    kategori: barang.kategori,
                    stok_tersedia: barang.stok_tersedia,
                    lokasi: barang.lokasi,
                    gambar: barang.gambar,
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
            const hBadge = document.getElementById('headerCartBadge');

            if (cart.length > 0) {
                floatBtn.style.display = 'flex';
                badge.textContent = cart.length;
                hBadge.style.display = 'grid';
                hBadge.textContent = cart.length;
            } else {
                floatBtn.style.display = 'none';
                hBadge.style.display = 'none';
            }
        }

        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target == document.getElementById('detailModal')) closeDetailModal();
        }
    </script>
</body>
</html>
