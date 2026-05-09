<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'kepala'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require '../koneksi.php';

$type = $_GET['type'] ?? '';
$startDate = $_GET['start'] ?? '';
$endDate = $_GET['end'] ?? '';

try {
    switch ($type) {
        case 'barang':
            $kat = $_GET['kategori'] ?? 'semua';
            $sql = "SELECT id_barang, nama_barang, kategori, stok_total, stok_tersedia, lokasi,
                           (stok_total - stok_tersedia) as sedang_dipinjam
                    FROM barang WHERE 1=1";
            $params = [];
            
            if ($kat !== 'semua') {
                $sql .= " AND kategori = ?";
                $params[] = $kat;
            }
            $sql .= " ORDER BY nama_barang ASC";
            
            $stmt = $koneksi->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format status
            foreach($data as &$row) {
                if ($row['stok_tersedia'] == 0 && $row['stok_total'] > 0) $row['status'] = 'Habis';
                else if ($row['stok_tersedia'] > 0 && $row['stok_tersedia'] <= 2) $row['status'] = 'Terbatas';
                else $row['status'] = 'Tersedia';
            }
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'peminjaman':
            $status = $_GET['status'] ?? 'semua';
            
            $sql = "SELECT p.id_peminjaman, u.nama_lengkap as peminjam, u.asal_ormawa, 
                           p.nama_kegiatan, p.tujuan, p.lokasi, p.tgl_pengajuan, p.status_approval,
                           p.alasan_tolak, a.nama_lengkap as admin_penyetuju,
                           (SELECT COUNT(*) FROM peminjaman_detail d WHERE d.id_peminjaman = p.id_peminjaman) as jumlah_item
                    FROM peminjaman p
                    JOIN users u ON p.id_user = u.id_user
                    LEFT JOIN users a ON p.id_admin = a.id_user
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) { $sql .= " AND DATE(p.tgl_pengajuan) >= ?"; $params[] = $startDate; }
            if ($endDate) { $sql .= " AND DATE(p.tgl_pengajuan) <= ?"; $params[] = $endDate; }
            if ($status !== 'semua') { $sql .= " AND p.status_approval = ?"; $params[] = $status; }
            
            $sql .= " ORDER BY p.tgl_pengajuan DESC";
            
            $stmt = $koneksi->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'pengembalian':
            $sql = "SELECT p.id_pengembalian, u.nama_lengkap as peminjam, b.nama_barang, 
                           d.jumlah, d.tgl_pinjam, d.tgl_kembali_rencana, p.tgl_kembali_asli,
                           p.kondisi_barang, p.denda, a.nama_lengkap as admin_penerima
                    FROM pengembalian p
                    JOIN peminjaman_detail d ON p.id_detail = d.id_detail
                    JOIN peminjaman pem ON d.id_peminjaman = pem.id_peminjaman
                    JOIN users u ON pem.id_user = u.id_user
                    JOIN barang b ON d.id_barang = b.id_barang
                    LEFT JOIN users a ON p.id_admin_penerima = a.id_user
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) { $sql .= " AND DATE(p.tgl_kembali_asli) >= ?"; $params[] = $startDate; }
            if ($endDate) { $sql .= " AND DATE(p.tgl_kembali_asli) <= ?"; $params[] = $endDate; }
            
            $sql .= " ORDER BY p.tgl_kembali_asli DESC";
            
            $stmt = $koneksi->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate keterlambatan
            foreach($data as &$row) {
                $ren = strtotime($row['tgl_kembali_rencana']);
                $asli = strtotime(date('Y-m-d', strtotime($row['tgl_kembali_asli'])));
                $diff = ($asli - $ren) / (60 * 60 * 24);
                $row['keterlambatan'] = $diff > 0 ? $diff : 0;
            }
            
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'anggota':
            $role = $_GET['role'] ?? 'semua';
            $sql = "SELECT u.id_user, u.nama_lengkap, u.username, u.role, u.no_hp, u.asal_ormawa, u.created_at,
                           (SELECT COUNT(*) FROM peminjaman p WHERE p.id_user = u.id_user) as total_peminjaman,
                           (SELECT SUM(pg.denda) FROM pengembalian pg 
                            JOIN peminjaman_detail d ON pg.id_detail = d.id_detail 
                            JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                            WHERE p.id_user = u.id_user) as total_denda
                    FROM users u WHERE 1=1";
            $params = [];
            
            if ($role !== 'semua') { $sql .= " AND u.role = ?"; $params[] = $role; }
            
            $sql .= " ORDER BY u.nama_lengkap ASC";
            
            $stmt = $koneksi->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // format denda
            foreach($data as &$row) {
                $row['total_denda'] = $row['total_denda'] ?: 0;
            }
            
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        default:
            throw new Exception('Invalid report type');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
