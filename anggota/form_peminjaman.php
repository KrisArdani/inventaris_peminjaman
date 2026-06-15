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
    <title>Form Pengajuan - Inventaris BEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="anggota.css?v=2.1">
</head>
<body>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon"><img src="../assets/images/logo bem.png" alt="Logo BEM KM Politeknik Purbaya" style="width: 32px; height: 32px; border-radius: 50%;"></div>
            <span>Inventaris BEM</span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Menu Utama</div>
            <a href="index.php" class="sidebar-link">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="katalog.php" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Katalog Inventaris
            </a>
            <a href="form_peminjaman.php" class="sidebar-link active">
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
                    <span class="current">Form Pengajuan Peminjaman</span>
                </div>
            </div>
            <div class="top-header-right">
                <div class="cart-indicator-btn" title="Pengajuan Aktif">
                    <i class="fa-solid fa-file-signature"></i>
                    <div class="cart-badge" id="headerCartBadge" style="display: none;">0</div>
                </div>
            </div>
        </header>

        <!-- Page Container -->
        <div class="page-container" style="max-width: 900px;">
            <div class="page-header" style="margin-bottom: 2rem;">
                <h1 class="page-title">Form Pengajuan Peminjaman</h1>
                <p class="page-subtitle">Konfirmasi inventaris terpilih dan lengkapi rincian kegiatan Anda.</p>
            </div>

            <div class="form-card">
                <!-- Cart Section -->
                <div class="cart-section">
                    <div class="cart-header">
                        <h2><i class="fa-solid fa-boxes-stacked" style="color: var(--primary);"></i> Inventaris Terpilih</h2>
                        <a href="katalog.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;"><i class="fa-solid fa-plus"></i> Tambah Barang</a>
                    </div>
                    <div id="cartItemsContainer" class="cart-items">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Form Inputs Section -->
                <div class="form-section" id="formSection" style="display: none;">
                    <form id="peminjamanForm" novalidate>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="nama_kegiatan">Nama Kegiatan / Agenda Acara</label>
                                <input type="text" id="nama_kegiatan" placeholder="Contoh: Rapat Koordinasi Wilayah BEM Politeknik Purbaya" required>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="tujuan">Tujuan & Rencana Keperluan</label>
                                <textarea id="tujuan" placeholder="Jelaskan tujuan peminjaman barang inventaris ini..." rows="3" required></textarea>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="lokasi">Lokasi Penggunaan Barang</label>
                                <input type="text" id="lokasi" placeholder="Contoh: Aula Kampus Purbaya / Ruang Rapat Sekretariat BEM" required>
                            </div>

                            <div class="form-group">
                                <label for="tgl_pinjam">Tanggal Mulai Peminjaman</label>
                                <input type="date" id="tgl_pinjam" required>
                            </div>
                            <div class="form-group">
                                <label for="tgl_kembali">Tanggal Pengembalian Rencana</label>
                                <input type="date" id="tgl_kembali" required>
                            </div>
                        </div>
                        
                        <label class="terms-checkbox" for="syarat_ketentuan">
                            <input type="checkbox" id="syarat_ketentuan" required>
                            <span>
                                <strong>Syarat & Ketentuan Peminjaman Inventaris:</strong><br>
                                1. Bertanggung jawab penuh atas kebersihan dan keutuhan barang selama masa peminjaman.<br>
                                2. Bersedia mengembalikan barang sesuai dengan tanggal rencana yang ditentukan.<br>
                                3. Wajib mengganti rugi secara mandiri apabila barang rusak atau hilang karena kelalaian.
                            </span>
                        </label>
                        
                        <button type="submit" class="btn btn-primary" id="btnSubmit" style="width: 100%; padding: 1rem; font-size: 1.05rem;">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Formulir Pengajuan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Set min date values
        const todayStr = new Date().toISOString().split('T')[0];
        document.getElementById('tgl_pinjam').min = todayStr;
        document.getElementById('tgl_kembali').min = todayStr;

        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
            document.getElementById('peminjamanForm').addEventListener('submit', submitPeminjaman);

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

        function getCart() {
            const c = localStorage.getItem('cart_peminjaman');
            return c ? JSON.parse(c) : [];
        }

        function saveCart(cart) {
            localStorage.setItem('cart_peminjaman', JSON.stringify(cart));
            updateHeaderCartBadge();
        }

        function updateHeaderCartBadge() {
            const cart = getCart();
            const badge = document.getElementById('headerCartBadge');
            if (cart.length > 0) {
                badge.style.display = 'grid';
                badge.textContent = cart.length;
            } else {
                badge.style.display = 'none';
            }
        }

        function adjustQty(id, diff) {
            let cart = getCart();
            const item = cart.find(i => i.id_barang === id);
            if (item) {
                let qty = parseInt(item.jumlah_pinjam) + diff;
                if (qty > parseInt(item.stok_tersedia)) {
                    qty = parseInt(item.stok_tersedia);
                    showToast('Batas maksimum stok tersedia tercapai', 'error');
                }
                if (qty < 1) qty = 1;
                item.jumlah_pinjam = qty;
                saveCart(cart);
                renderCart();
            }
        }

        function removeFromCart(id) {
            let cart = getCart();
            cart = cart.filter(item => item.id_barang !== id);
            saveCart(cart);
            renderCart();
            showToast('Barang berhasil dihapus dari daftar pilihan', 'success');
        }

        function renderCart() {
            const cart = getCart();
            const container = document.getElementById('cartItemsContainer');
            const formSection = document.getElementById('formSection');

            updateHeaderCartBadge();

            if (cart.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding: 4rem 2rem; color: var(--on-surface-variant);">
                        <i class="fa-solid fa-basket-shopping fa-3x" style="color: var(--outline); margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 1.2rem; font-weight:700; color: var(--on-surface);">Daftar Pengajuan Kosong</h3>
                        <p style="font-size: 0.9rem; margin-top:0.25rem;">Silakan pilih barang inventaris terlebih dahulu melalui katalog.</p>
                        <a href="katalog.php" class="btn btn-primary" style="margin-top: 1.5rem;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog</a>
                    </div>
                `;
                formSection.style.display = 'none';
                return;
            }

            formSection.style.display = 'block';
            container.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="item-info">
                        <h3 style="font-size: 1.05rem; font-weight: 700;">${item.nama_barang}</h3>
                        <span class="item-meta" style="font-size: 0.8rem; color: var(--on-surface-variant);">
                            Tersedia: ${item.stok_tersedia} | Kategori: ${item.kategori}
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div class="qty-control">
                            <button type="button" class="qty-btn" onclick="adjustQty('${item.id_barang}', -1)">-</button>
                            <input type="text" class="qty-input" value="${item.jumlah_pinjam}" readonly>
                            <button type="button" class="qty-btn" onclick="adjustQty('${item.id_barang}', 1)">+</button>
                        </div>
                        <button type="button" class="btn-remove" onclick="removeFromCart('${item.id_barang}')" title="Hapus Barang">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        async function submitPeminjaman(e) {
            e.preventDefault();
            const cart = getCart();
            if (cart.length === 0) return;

            const nama_kegiatan = document.getElementById('nama_kegiatan').value.trim();
            const tujuan = document.getElementById('tujuan').value.trim();
            const lokasi = document.getElementById('lokasi').value.trim();
            const tgl_pinjam = document.getElementById('tgl_pinjam').value;
            const tgl_kembali = document.getElementById('tgl_kembali').value;
            const termsChecked = document.getElementById('syarat_ketentuan').checked;

            if (!nama_kegiatan || !tujuan || !lokasi || !tgl_pinjam || !tgl_kembali) {
                showToast('Lengkapi semua rincian formulir pengajuan', 'error');
                return;
            }

            if (!termsChecked) {
                showToast('Anda harus menyetujui Syarat & Ketentuan peminjaman', 'error');
                return;
            }

            if (tgl_kembali < tgl_pinjam) {
                showToast('Tanggal kembali tidak boleh mendahului tanggal pinjam', 'error');
                return;
            }

            const payload = {
                nama_kegiatan,
                tujuan,
                lokasi,
                tgl_pinjam,
                tgl_kembali,
                items: cart.map(item => ({
                    id_barang: item.id_barang,
                    jumlah: item.jumlah_pinjam
                }))
            };

            const btn = document.getElementById('btnSubmit');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim Pengajuan...';
            btn.disabled = true;

            try {
                const res = await fetch('api_anggota.php?action=ajukan_peminjaman', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    localStorage.removeItem('cart_peminjaman');
                    alert('Pengajuan peminjaman barang inventaris berhasil dikirim! Menunggu konfirmasi Admin.');
                    window.location.href = 'index.php';
                } else {
                    showToast(data.message || 'Gagal mengirim pengajuan', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan jaringan, hubungi admin', 'error');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        function showToast(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type === 'error' ? 'error' : ''}`;
            const icon = type === 'error' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-circle-check';
            toast.innerHTML = `<i class="${icon}"></i><span>${msg}</span>`;
            container.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => {
                toast.classList.add('show');
            }, 50);

            // Auto dismiss
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3500);
        }
    </script>
</body>
</html>
