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

try {
    switch ($action) {

        /* =============== LIST =============== */
        case 'list':
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page   = max(1, intval($_GET['page'] ?? 1));
            $limit  = 10;
            $offset = ($page - 1) * $limit;

            $where  = [];
            $params = [];

            if ($search) {
                $where[] = "(u.nama_lengkap LIKE :s1 OR p.id_peminjaman LIKE :s2)";
                $params[':s1'] = "%$search%";
                $params[':s2'] = "%$search%";
            }
            if ($status) {
                $where[] = "p.status_approval = :status";
                $params[':status'] = $status;
            }

            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Count
            $cntSQL = "SELECT COUNT(*) FROM peminjaman p JOIN users u ON p.id_user = u.id_user $whereSQL";
            $cnt = $koneksi->prepare($cntSQL);
            $cnt->execute($params);
            $total = $cnt->fetchColumn();

            // Data
            $sql = "SELECT p.*, u.nama_lengkap, u.username,
                        (SELECT COUNT(*) FROM peminjaman_detail d WHERE d.id_peminjaman = p.id_peminjaman) AS jumlah_item,
                        (SELECT GROUP_CONCAT(b.nama_barang SEPARATOR ', ')
                         FROM peminjaman_detail d
                         JOIN barang b ON d.id_barang = b.id_barang
                         WHERE d.id_peminjaman = p.id_peminjaman) AS daftar_barang
                    FROM peminjaman p
                    JOIN users u ON p.id_user = u.id_user
                    $whereSQL
                    ORDER BY p.tgl_pengajuan DESC
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
            $totalAll = $koneksi->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
            $pending  = $koneksi->query("SELECT COUNT(*) FROM peminjaman WHERE status_approval='pending'")->fetchColumn();
            $approved = $koneksi->query("SELECT COUNT(*) FROM peminjaman WHERE status_approval='disetujui'")->fetchColumn();
            $rejected = $koneksi->query("SELECT COUNT(*) FROM peminjaman WHERE status_approval='ditolak'")->fetchColumn();
            $terlambat = $koneksi->query("SELECT COUNT(DISTINCT pd.id_peminjaman) FROM peminjaman_detail pd WHERE pd.status_item='terlambat'")->fetchColumn();

            echo json_encode([
                'success'   => true,
                'total'     => (int)$totalAll,
                'pending'   => (int)$pending,
                'approved'  => (int)$approved,
                'rejected'  => (int)$rejected,
                'terlambat' => (int)$terlambat,
            ]);
            break;

        /* =============== DETAIL =============== */
        case 'detail':
            $id = $_GET['id'] ?? '';
            if (!$id) throw new Exception('ID peminjaman diperlukan');

            $sql = "SELECT p.*, u.nama_lengkap, u.username, u.no_hp, u.asal_ormawa,
                        a.nama_lengkap AS admin_nama
                    FROM peminjaman p
                    JOIN users u ON p.id_user = u.id_user
                    LEFT JOIN users a ON p.id_admin = a.id_user
                    WHERE p.id_peminjaman = ?";
            $stmt = $koneksi->prepare($sql);
            $stmt->execute([$id]);
            $peminjaman = $stmt->fetch();

            if (!$peminjaman) throw new Exception('Data peminjaman tidak ditemukan');

            // Detail items
            $sqlD = "SELECT d.*, b.nama_barang, b.kategori, b.lokasi
                     FROM peminjaman_detail d
                     JOIN barang b ON d.id_barang = b.id_barang
                     WHERE d.id_peminjaman = ?
                     ORDER BY d.id_detail";
            $stmtD = $koneksi->prepare($sqlD);
            $stmtD->execute([$id]);
            $items = $stmtD->fetchAll();

            echo json_encode([
                'success'    => true,
                'peminjaman' => $peminjaman,
                'items'      => $items,
            ]);
            break;

        /* =============== APPROVE =============== */
        case 'approve':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id_peminjaman'] ?? '';
            if (!$id) throw new Exception('ID peminjaman diperlukan');

            $koneksi->beginTransaction();

            $stmt = $koneksi->prepare("UPDATE peminjaman SET status_approval='disetujui', id_admin=?, alasan_tolak=NULL WHERE id_peminjaman=? AND status_approval='pending'");
            $stmt->execute([$_SESSION['user_id'], $id]);

            if ($stmt->rowCount() === 0) {
                $koneksi->rollBack();
                throw new Exception('Peminjaman tidak ditemukan atau sudah diproses');
            }

            // Kurangi stok_tersedia
            $items = $koneksi->prepare("SELECT id_barang, jumlah FROM peminjaman_detail WHERE id_peminjaman=?");
            $items->execute([$id]);
            foreach ($items->fetchAll() as $item) {
                $upd = $koneksi->prepare("UPDATE barang SET stok_tersedia = stok_tersedia - ? WHERE id_barang = ? AND stok_tersedia >= ?");
                $upd->execute([$item['jumlah'], $item['id_barang'], $item['jumlah']]);
                if ($upd->rowCount() === 0) {
                    $koneksi->rollBack();
                    throw new Exception('Stok tidak mencukupi untuk barang ' . $item['id_barang']);
                }
            }

            $koneksi->commit();
            echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil disetujui']);
            break;

        /* =============== REJECT =============== */
        case 'reject':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);
            $id     = $data['id_peminjaman'] ?? '';
            $alasan = $data['alasan'] ?? '';
            if (!$id) throw new Exception('ID peminjaman diperlukan');

            $stmt = $koneksi->prepare("UPDATE peminjaman SET status_approval='ditolak', id_admin=?, alasan_tolak=? WHERE id_peminjaman=? AND status_approval='pending'");
            $stmt->execute([$_SESSION['user_id'], $alasan, $id]);

            if ($stmt->rowCount() === 0) throw new Exception('Peminjaman tidak ditemukan atau sudah diproses');

            echo json_encode(['success' => true, 'message' => 'Peminjaman ditolak']);
            break;

        /* =============== BARANG LIST (for form) =============== */
        case 'barang_list':
            $rows = $koneksi->query("SELECT id_barang, nama_barang, stok_tersedia, kategori FROM barang WHERE stok_tersedia > 0 ORDER BY nama_barang")->fetchAll();
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        /* =============== USERS LIST (anggota) =============== */
        case 'users_list':
            $rows = $koneksi->query("SELECT id_user, nama_lengkap, username FROM users WHERE role='anggota' ORDER BY nama_lengkap")->fetchAll();
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        /* =============== CREATE (admin buat peminjaman) =============== */
        case 'create':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);

            $id_user     = $data['id_user'] ?? '';
            $items       = $data['items'] ?? [];
            $tgl_pinjam  = $data['tgl_pinjam'] ?? '';
            $tgl_kembali = $data['tgl_kembali'] ?? '';

            if (!$id_user || empty($items) || !$tgl_pinjam || !$tgl_kembali) {
                throw new Exception('Data tidak lengkap');
            }

            $koneksi->beginTransaction();

            // Generate ID
            $id_peminjaman = 'PMJ-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

            $stmt = $koneksi->prepare("INSERT INTO peminjaman (id_peminjaman, id_user, status_approval, id_admin) VALUES (?, ?, 'disetujui', ?)");
            $stmt->execute([$id_peminjaman, $id_user, $_SESSION['user_id']]);

            foreach ($items as $item) {
                $id_detail = 'DTL-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $stmtD = $koneksi->prepare("INSERT INTO peminjaman_detail (id_detail, id_peminjaman, id_barang, jumlah, tgl_pinjam, tgl_kembali_rencana, status_item) VALUES (?,?,?,?,?,?,'dipinjam')");
                $stmtD->execute([$id_detail, $id_peminjaman, $item['id_barang'], $item['jumlah'], $tgl_pinjam, $tgl_kembali]);

                // Kurangi stok
                $upd = $koneksi->prepare("UPDATE barang SET stok_tersedia = stok_tersedia - ? WHERE id_barang = ? AND stok_tersedia >= ?");
                $upd->execute([$item['jumlah'], $item['id_barang'], $item['jumlah']]);
                if ($upd->rowCount() === 0) {
                    $koneksi->rollBack();
                    throw new Exception('Stok tidak mencukupi untuk barang ' . $item['id_barang']);
                }
            }

            $koneksi->commit();
            echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil dibuat']);
            break;

        default:
            throw new Exception('Action tidak valid');
    }
} catch (Exception $e) {
    if ($koneksi->inTransaction()) $koneksi->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
