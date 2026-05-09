<?php
session_start();
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'anggota') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'katalog':
            // Fetch all items, ordered by availability and name
            $sql = "SELECT id_barang, nama_barang, kategori, stok_tersedia, lokasi, gambar 
                    FROM barang 
                    ORDER BY (stok_tersedia > 0) DESC, nama_barang ASC";
            $stmt = $koneksi->query($sql);
            $rows = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        case 'ajukan_peminjaman':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);

            $id_user     = $_SESSION['user_id'];
            $items       = $data['items'] ?? [];
            $tgl_pinjam  = $data['tgl_pinjam'] ?? '';
            $tgl_kembali = $data['tgl_kembali'] ?? '';
            $nama_kegiatan = $data['nama_kegiatan'] ?? '';
            $tujuan      = $data['tujuan'] ?? '';
            $lokasi      = $data['lokasi'] ?? '';

            if (empty($items) || !$tgl_pinjam || !$tgl_kembali || !$nama_kegiatan || !$tujuan || !$lokasi) {
                throw new Exception('Data pengajuan tidak lengkap');
            }

            // Validasi tanggal
            $today = date('Y-m-d');
            if ($tgl_pinjam < $today) {
                throw new Exception('Tanggal pinjam tidak boleh di masa lalu');
            }
            if ($tgl_kembali < $tgl_pinjam) {
                throw new Exception('Tanggal kembali tidak valid');
            }

            $koneksi->beginTransaction();

            // Generate ID Peminjaman
            $id_peminjaman = 'PMJ-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

            $stmt = $koneksi->prepare("INSERT INTO peminjaman (id_peminjaman, id_user, status_approval, nama_kegiatan, tujuan, lokasi) VALUES (?, ?, 'pending', ?, ?, ?)");
            $stmt->execute([$id_peminjaman, $id_user, $nama_kegiatan, $tujuan, $lokasi]);

            // Cek ketersediaan stok sementara dan insert detail
            foreach ($items as $item) {
                // Pastikan stok ada saat diajukan
                $cek = $koneksi->prepare("SELECT stok_tersedia FROM barang WHERE id_barang = ?");
                $cek->execute([$item['id_barang']]);
                $stok = $cek->fetchColumn();

                if ($stok < $item['jumlah']) {
                    $koneksi->rollBack();
                    throw new Exception("Stok barang {$item['id_barang']} tidak mencukupi saat ini.");
                }

                $id_detail = 'DTL-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $stmtD = $koneksi->prepare("INSERT INTO peminjaman_detail (id_detail, id_peminjaman, id_barang, jumlah, tgl_pinjam, tgl_kembali_rencana, status_item) VALUES (?,?,?,?,?,?,'dipinjam')");
                $stmtD->execute([$id_detail, $id_peminjaman, $item['id_barang'], $item['jumlah'], $tgl_pinjam, $tgl_kembali]);
            }

            $koneksi->commit();
            echo json_encode(['success' => true, 'message' => 'Pengajuan peminjaman berhasil dikirim']);
            break;

        case 'stats':
            $id_user = $_SESSION['user_id'];
            
            // Total pengajuan
            $total = $koneksi->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_user = ?");
            $total->execute([$id_user]);
            
            // Pending
            $pending = $koneksi->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_user = ? AND status_approval = 'pending'");
            $pending->execute([$id_user]);
            
            // Sedang dipinjam (menghitung item dari peminjaman_detail)
            $aktif = $koneksi->prepare("SELECT COUNT(DISTINCT p.id_peminjaman) 
                                       FROM peminjaman p 
                                       JOIN peminjaman_detail d ON p.id_peminjaman = d.id_peminjaman 
                                       WHERE p.id_user = ? AND d.status_item = 'dipinjam'");
            $aktif->execute([$id_user]);

            // Terlambat
            $terlambat = $koneksi->prepare("SELECT COUNT(DISTINCT p.id_peminjaman) 
                                           FROM peminjaman p 
                                           JOIN peminjaman_detail d ON p.id_peminjaman = d.id_peminjaman 
                                           WHERE p.id_user = ? AND d.status_item = 'terlambat'");
            $terlambat->execute([$id_user]);

            echo json_encode([
                'success' => true,
                'total' => (int)$total->fetchColumn(),
                'pending' => (int)$pending->fetchColumn(),
                'aktif' => (int)$aktif->fetchColumn(),
                'terlambat' => (int)$terlambat->fetchColumn()
            ]);
            break;

        case 'riwayat':
            $id_user = $_SESSION['user_id'];
            $sql = "SELECT p.*, 
                        (SELECT COUNT(*) FROM peminjaman_detail d WHERE d.id_peminjaman = p.id_peminjaman) AS jumlah_item,
                        (SELECT COUNT(*) FROM peminjaman_detail d WHERE d.id_peminjaman = p.id_peminjaman AND d.status_item = 'dikembalikan') AS jumlah_dikembalikan,
                        (SELECT GROUP_CONCAT(b.nama_barang SEPARATOR ', ')
                         FROM peminjaman_detail d
                         JOIN barang b ON d.id_barang = b.id_barang
                         WHERE d.id_peminjaman = p.id_peminjaman) AS daftar_barang
                    FROM peminjaman p
                    WHERE p.id_user = ?
                    ORDER BY p.tgl_pengajuan DESC";
            $stmt = $koneksi->prepare($sql);
            $stmt->execute([$id_user]);
            $rows = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        case 'detail_riwayat':
            $id_user = $_SESSION['user_id'];
            $id_peminjaman = $_GET['id'] ?? '';
            
            // Verifikasi milik user sendiri
            $cek = $koneksi->prepare("SELECT p.*, 
                                        (SELECT nama_lengkap FROM users u WHERE u.id_user = p.id_admin) AS admin_nama,
                                        (SELECT COUNT(*) FROM peminjaman_detail d WHERE d.id_peminjaman = p.id_peminjaman) AS jumlah_item,
                                        (SELECT COUNT(*) FROM peminjaman_detail d WHERE d.id_peminjaman = p.id_peminjaman AND d.status_item = 'dikembalikan') AS jumlah_dikembalikan
                                      FROM peminjaman p 
                                      WHERE p.id_peminjaman = ? AND p.id_user = ?");
            $cek->execute([$id_peminjaman, $id_user]);
            $peminjaman = $cek->fetch(PDO::FETCH_ASSOC);
            
            if (!$peminjaman) {
                throw new Exception('Data peminjaman tidak ditemukan');
            }

            $sqlD = "SELECT d.*, b.nama_barang, b.kategori 
                     FROM peminjaman_detail d
                     JOIN barang b ON d.id_barang = b.id_barang
                     WHERE d.id_peminjaman = ?";
            $stmtD = $koneksi->prepare($sqlD);
            $stmtD->execute([$id_peminjaman]);
            $items = $stmtD->fetchAll();

            echo json_encode([
                'success' => true, 
                'peminjaman' => $peminjaman,
                'items' => $items
            ]);
            break;

        default:
            throw new Exception('Action tidak valid');
    }
} catch (Exception $e) {
    if ($koneksi->inTransaction()) $koneksi->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
