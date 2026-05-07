<?php
session_start();
header('Content-Type: application/json');

// Auth check
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

        // ---- LIST / SEARCH ----
        case 'list':
            $search   = $_GET['search'] ?? '';
            $kategori = $_GET['kategori'] ?? '';
            $page     = max(1, intval($_GET['page'] ?? 1));
            $limit    = 10;
            $offset   = ($page - 1) * $limit;

            $where = [];
            $params = [];

            if ($search) {
                $where[] = "(nama_barang LIKE :search OR id_barang LIKE :search2)";
                $params[':search']  = "%$search%";
                $params[':search2'] = "%$search%";
            }
            if ($kategori) {
                $where[] = "kategori = :kategori";
                $params[':kategori'] = $kategori;
            }

            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Count
            $countStmt = $koneksi->prepare("SELECT COUNT(*) FROM barang $whereSQL");
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Data
            $sql = "SELECT * FROM barang $whereSQL ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
            $stmt = $koneksi->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data'    => $rows,
                'total'   => (int)$total,
                'page'    => $page,
                'pages'   => ceil($total / $limit),
            ]);
            break;

        // ---- STATS ----
        case 'stats':
            $total     = $koneksi->query("SELECT COUNT(*) FROM barang")->fetchColumn();
            $tersedia  = $koneksi->query("SELECT SUM(stok_tersedia) FROM barang")->fetchColumn();
            $dipinjam  = $koneksi->query("SELECT SUM(stok_total - stok_tersedia) FROM barang")->fetchColumn();
            $habis     = $koneksi->query("SELECT COUNT(*) FROM barang WHERE stok_tersedia = 0")->fetchColumn();

            echo json_encode([
                'success'   => true,
                'total'     => (int)$total,
                'tersedia'  => (int)$tersedia,
                'dipinjam'  => (int)$dipinjam,
                'habis'     => (int)$habis,
            ]);
            break;

        // ---- CREATE ----
        case 'create':
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $data = json_decode(file_get_contents('php://input'), true);
            $id   = $data['id_barang'] ?? '';
            $nama = $data['nama_barang'] ?? '';
            $kat  = $data['kategori'] ?? '';
            $stok = intval($data['stok_total'] ?? 0);
            $ters = intval($data['stok_tersedia'] ?? 0);
            $lok  = $data['lokasi'] ?? '';

            if (!$id || !$nama || !$kat) throw new Exception('Data tidak lengkap');
            if ($ters > $stok) throw new Exception('Stok tersedia tidak boleh melebihi stok total');

            // Check duplicate
            $chk = $koneksi->prepare("SELECT COUNT(*) FROM barang WHERE id_barang = ?");
            $chk->execute([$id]);
            if ($chk->fetchColumn() > 0) throw new Exception('Kode barang sudah digunakan');

            $stmt = $koneksi->prepare("INSERT INTO barang (id_barang, nama_barang, kategori, stok_total, stok_tersedia, lokasi) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$id, $nama, $kat, $stok, $ters, $lok]);

            echo json_encode(['success' => true, 'message' => 'Barang berhasil ditambahkan']);
            break;

        // ---- UPDATE ----
        case 'update':
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $data = json_decode(file_get_contents('php://input'), true);
            $id   = $data['id_barang'] ?? '';
            $nama = $data['nama_barang'] ?? '';
            $kat  = $data['kategori'] ?? '';
            $stok = intval($data['stok_total'] ?? 0);
            $ters = intval($data['stok_tersedia'] ?? 0);
            $lok  = $data['lokasi'] ?? '';

            if (!$id || !$nama || !$kat) throw new Exception('Data tidak lengkap');
            if ($ters > $stok) throw new Exception('Stok tersedia tidak boleh melebihi stok total');

            $stmt = $koneksi->prepare("UPDATE barang SET nama_barang=?, kategori=?, stok_total=?, stok_tersedia=?, lokasi=? WHERE id_barang=?");
            $stmt->execute([$nama, $kat, $stok, $ters, $lok, $id]);

            echo json_encode(['success' => true, 'message' => 'Barang berhasil diperbarui']);
            break;

        // ---- DELETE ----
        case 'delete':
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id_barang'] ?? '';
            if (!$id) throw new Exception('ID barang diperlukan');

            $stmt = $koneksi->prepare("DELETE FROM barang WHERE id_barang = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Barang berhasil dihapus']);
            break;

        default:
            throw new Exception('Action tidak valid');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
