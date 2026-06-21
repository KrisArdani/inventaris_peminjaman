<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Auto update status terlambat
try {
    $koneksi->exec("UPDATE peminjaman_detail SET status_item = 'terlambat' WHERE status_item = 'dipinjam' AND tgl_kembali_rencana < CURDATE()");
} catch (Exception $e) {
    // ignore
}

try {
    switch ($action) {

        /* =============== LIST =============== */
        case 'list':
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? 'belum'; // belum | sudah
            $page   = max(1, intval($_GET['page'] ?? 1));
            $limit  = 10;
            $offset = ($page - 1) * $limit;

            $where  = ["p.status_approval = 'disetujui'"];
            $params = [];

            if ($search) {
                $where[] = "(u.nama_lengkap LIKE :s1 OR p.id_peminjaman LIKE :s2 OR b.nama_barang LIKE :s3)";
                $params[':s1'] = "%$search%";
                $params[':s2'] = "%$search%";
                $params[':s3'] = "%$search%";
            }

            if ($status === 'belum') {
                $where[] = "pd.status_item IN ('dipinjam', 'terlambat')";
            } elseif ($status === 'sudah') {
                $where[] = "pd.status_item = 'dikembalikan'";
            }

            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Count
            $cntSQL = "SELECT COUNT(*) FROM peminjaman_detail pd 
                       JOIN peminjaman p ON pd.id_peminjaman = p.id_peminjaman 
                       JOIN users u ON p.id_user = u.id_user 
                       JOIN barang b ON pd.id_barang = b.id_barang 
                       $whereSQL";
            $cnt = $koneksi->prepare($cntSQL);
            $cnt->execute($params);
            $total = $cnt->fetchColumn();

            // Data
            $sql = "SELECT pd.*, p.id_user, u.nama_lengkap, u.username, b.nama_barang, 
                           pg.tgl_kembali_asli, pg.kondisi_barang, pg.denda, a.nama_lengkap as nama_admin_penerima
                    FROM peminjaman_detail pd
                    JOIN peminjaman p ON pd.id_peminjaman = p.id_peminjaman
                    JOIN users u ON p.id_user = u.id_user
                    JOIN barang b ON pd.id_barang = b.id_barang
                    LEFT JOIN pengembalian pg ON pd.id_detail = pg.id_detail
                    LEFT JOIN users a ON pg.id_admin_penerima = a.id_user
                    $whereSQL
                    ORDER BY 
                        CASE WHEN pd.status_item = 'terlambat' THEN 1
                             WHEN pd.status_item = 'dipinjam' THEN 2
                             ELSE 3 END, 
                        pd.tgl_kembali_rencana ASC,
                        pg.tgl_kembali_asli DESC
                    LIMIT $limit OFFSET $offset";
            $stmt = $koneksi->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data'    => $rows,
                'total'   => (int)$total,
                'page'    => $page,
                'pages'   => max(1, ceil($total / $limit)),
            ]);
            break;

        /* =============== STATS =============== */
        case 'stats':
            $time_filter = $_GET['time_filter'] ?? 'bulan_ini';
            
            $aktif     = $koneksi->query("SELECT COUNT(*) FROM peminjaman_detail WHERE status_item IN ('dipinjam', 'terlambat')")->fetchColumn();
            $terlambat = $koneksi->query("SELECT COUNT(*) FROM peminjaman_detail WHERE status_item = 'terlambat'")->fetchColumn();
            
            if ($time_filter === 'all_time') {
                $dikembalikan = $koneksi->query("SELECT COUNT(DISTINCT id_detail) FROM pengembalian")->fetchColumn();
                $denda    = $koneksi->query("SELECT SUM(denda) FROM pengembalian")->fetchColumn();
            } else {
                $dikembalikan = $koneksi->query("SELECT COUNT(DISTINCT id_detail) FROM pengembalian WHERE MONTH(tgl_kembali_asli) = MONTH(CURRENT_DATE()) AND YEAR(tgl_kembali_asli) = YEAR(CURRENT_DATE())")->fetchColumn();
                $denda    = $koneksi->query("SELECT SUM(denda) FROM pengembalian WHERE MONTH(tgl_kembali_asli) = MONTH(CURRENT_DATE()) AND YEAR(tgl_kembali_asli) = YEAR(CURRENT_DATE())")->fetchColumn();
            }

            echo json_encode([
                'success'      => true,
                'aktif'        => (int)$aktif,
                'terlambat'    => (int)$terlambat,
                'dikembalikan' => (int)$dikembalikan,
                'denda'        => (float)$denda,
            ]);
            break;

        /* =============== PROSES KEMBALI =============== */
        case 'proses':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id_detail = $data['id_detail'] ?? '';
            $kondisi   = $data['kondisi'] ?? '';
            $denda     = floatval($data['denda'] ?? 0);
            
            if (!$id_detail || !$kondisi) throw new Exception('Data tidak lengkap');

            $koneksi->beginTransaction();

            // Cek detail
            $stmtDetail = $koneksi->prepare("SELECT * FROM peminjaman_detail WHERE id_detail = ? AND status_item IN ('dipinjam', 'terlambat') FOR UPDATE");
            $stmtDetail->execute([$id_detail]);
            $detail = $stmtDetail->fetch();

            if (!$detail) {
                $koneksi->rollBack();
                throw new Exception('Data peminjaman tidak valid atau sudah dikembalikan');
            }

            // Insert ke tabel pengembalian
            $id_pengembalian = 'KMB-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $stmtPg = $koneksi->prepare("INSERT INTO pengembalian (id_pengembalian, id_detail, kondisi_barang, denda, id_admin_penerima) VALUES (?, ?, ?, ?, ?)");
            $stmtPg->execute([$id_pengembalian, $id_detail, $kondisi, $denda, $_SESSION['user_id']]);

            // Update status di peminjaman_detail
            $stmtUpdDetail = $koneksi->prepare("UPDATE peminjaman_detail SET status_item = 'dikembalikan' WHERE id_detail = ?");
            $stmtUpdDetail->execute([$id_detail]);

            // Kembalikan stok barang
            $stmtUpdBarang = $koneksi->prepare("UPDATE barang SET stok_tersedia = stok_tersedia + ? WHERE id_barang = ?");
            $stmtUpdBarang->execute([$detail['jumlah'], $detail['id_barang']]);

            // Jika kondisi barang tidak baik, bisa dicatat, tapi stok dikembalikan.
            // Alternatifnya jika rusak berat, stok tidak dikembalikan atau status barang berubah.
            // Untuk simplifikasi, stok kembali, admin bisa edit manual jika perlu disisihkan.

            $koneksi->commit();
            echo json_encode(['success' => true, 'message' => 'Pengembalian berhasil diproses']);
            break;

        default:
            throw new Exception('Action tidak valid');
    }
} catch (Exception $e) {
    if ($koneksi->inTransaction()) $koneksi->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
