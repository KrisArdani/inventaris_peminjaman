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
    <title>Form Peminjaman - Inventaris BEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { 
            padding-top: 100px; 
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4 {
            font-family: 'Manrope', sans-serif;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #0f172a;
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .page-header p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .form-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .cart-section {
            padding: 2.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .cart-header h2 {
            font-size: 1.5rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-tambah {
            background: #f0fdf4;
            color: var(--primary);
            border: 1px solid #bbf7d0;
            padding: 0.6rem 1.25rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-tambah:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .item-info h3 {
            font-size: 1.15rem;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .item-meta {
            font-size: 0.85rem;
            color: #64748b;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
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
            width: 40px;
            height: 40px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .btn-remove:hover {
            background: #fee2e2;
            transform: scale(1.05);
        }

        .form-section {
            padding: 2.5rem;
            background: #f8fafc;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .form-group label {
            font-weight: 700;
            color: #334155;
            font-size: 0.95rem;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group input[type="date"] {
            padding: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            color: #0f172a;
            background: white;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 16px;
        }

        .terms-checkbox input[type="checkbox"] {
            margin-top: 0.25rem;
            width: 1.25rem;
            height: 1.25rem;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .terms-checkbox label {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #166534;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 1.25rem;
            border-radius: 16px;
            font-size: 1.15rem;
            font-weight: 800;
            font-family: 'Manrope', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -3px rgba(16, 185, 129, 0.4);
        }

        .btn-submit:disabled {
            background: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #334155;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar scrolled">
        <div class="nav-container">
            <div class="nav-logo">
                <div class="logo-circle" style="background-color: var(--primary);"><i class="fa-solid fa-user"></i></div>
                <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Dashboard</a>
                <a href="katalog.php" class="nav-link">Katalog Barang</a>
                <a href="../logout.php" class="nav-link btn-contact"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Form Peminjaman</h1>
            <p>Konfirmasi barang yang ingin Anda pinjam dan tentukan tanggalnya.</p>
        </div>

        <div class="form-card">
            <div class="cart-section">
                <div class="cart-header">
                    <h2><i class="fa-solid fa-box-open" style="color: var(--primary);"></i> Barang Terpilih</h2>
                    <a href="katalog.php" class="btn-tambah"><i class="fa-solid fa-plus"></i> Tambah Barang Lain</a>
                </div>

                <div id="cartItemsContainer" class="cart-items">
                    <!-- Injected via JS -->
                </div>
            </div>

            <div class="form-section" id="formSection" style="display: none;">
                <form id="peminjamanForm">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="nama_kegiatan">Nama Kegiatan / Acara</label>
                            <input type="text" id="nama_kegiatan" placeholder="Contoh: Rapat Kerja BEM 2026" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="tujuan">Tujuan / Keperluan Peminjaman</label>
                            <textarea id="tujuan" placeholder="Jelaskan secara singkat untuk apa barang ini digunakan..." required></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="lokasi">Lokasi Penggunaan (Tempat Acara)</label>
                            <input type="text" id="lokasi" placeholder="Contoh: Gedung A Ruang 101" required>
                        </div>

                        <div class="form-group">
                            <label for="tgl_pinjam">Tanggal Pinjam</label>
                            <input type="date" id="tgl_pinjam" required>
                        </div>
                        <div class="form-group">
                            <label for="tgl_kembali">Tanggal Kembali (Rencana)</label>
                            <input type="date" id="tgl_kembali" required>
                        </div>
                    </div>
                    
                    <div class="terms-checkbox">
                        <input type="checkbox" id="syarat_ketentuan" required>
                        <label for="syarat_ketentuan">
                            <strong>Saya menyetujui Syarat & Ketentuan Peminjaman:</strong><br>
                            1. Bersedia menjaga barang dengan baik selama masa peminjaman.<br>
                            2. Akan mengembalikan barang tepat waktu sesuai tanggal rencana kembali.<br>
                            3. Bersedia mengganti rugi jika terjadi kerusakan atau kehilangan barang akibat kelalaian pribadi/kepanitiaan.
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <i class="fa-solid fa-paper-plane"></i> Ajukan Peminjaman Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tgl_pinjam').min = today;
        document.getElementById('tgl_kembali').min = today;

        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
            document.getElementById('peminjamanForm').addEventListener('submit', submitPeminjaman);
        });

        function getCart() {
            const c = localStorage.getItem('cart_peminjaman');
            return c ? JSON.parse(c) : [];
        }

        function saveCart(cart) {
            localStorage.setItem('cart_peminjaman', JSON.stringify(cart));
        }

        function updateCartQty(id, qty) {
            let cart = getCart();
            const item = cart.find(i => i.id_barang === id);
            if (item) {
                qty = parseInt(qty);
                if (qty > parseInt(item.stok_tersedia)) qty = parseInt(item.stok_tersedia);
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
        }

        function renderCart() {
            const cart = getCart();
            const container = document.getElementById('cartItemsContainer');
            const formSection = document.getElementById('formSection');

            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-basket-shopping"></i>
                        <h3>Belum ada barang dipilih</h3>
                        <p style="color: #64748b;">Silakan kembali ke katalog untuk memilih barang.</p>
                        <a href="katalog.php" class="btn-tambah" style="margin-top: 1.5rem; padding: 0.8rem 2rem;">Ke Katalog</a>
                    </div>
                `;
                formSection.style.display = 'none';
                return;
            }

            formSection.style.display = 'block';
            container.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="item-info">
                        <h3>${item.nama_barang}</h3>
                        <span class="item-meta">Tersedia: ${item.stok_tersedia} | Kategori: ${item.kategori}</span>
                    </div>
                    <div class="item-actions">
                        <div class="qty-control">
                            <span class="qty-label">Jumlah:</span>
                            <input type="number" class="qty-input" value="${item.jumlah_pinjam}" min="1" max="${item.stok_tersedia}" onchange="updateCartQty('${item.id_barang}', this.value)">
                        </div>
                        <button type="button" class="btn-remove" onclick="removeFromCart('${item.id_barang}')" title="Hapus Barang">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        async function submitPeminjaman(e) {
            e.preventDefault();
            const cart = getCart();
            if (cart.length === 0) return;

            const nama_kegiatan = document.getElementById('nama_kegiatan').value;
            const tujuan = document.getElementById('tujuan').value;
            const lokasi = document.getElementById('lokasi').value;
            const tgl_pinjam = document.getElementById('tgl_pinjam').value;
            const tgl_kembali = document.getElementById('tgl_kembali').value;
            const termsChecked = document.getElementById('syarat_ketentuan').checked;

            if (!termsChecked) {
                alert('Anda harus menyetujui Syarat & Ketentuan Peminjaman.');
                return;
            }

            if (tgl_kembali < tgl_pinjam) {
                alert('Tanggal kembali tidak boleh lebih awal dari tanggal pinjam.');
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
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
            btn.disabled = true;

            try {
                const res = await fetch('api_anggota.php?action=ajukan_peminjaman', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    localStorage.removeItem('cart_peminjaman'); // clear cart
                    alert('Pengajuan berhasil dibuat! Menunggu persetujuan admin.');
                    window.location.href = 'index.php'; // redirect to dashboard
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
