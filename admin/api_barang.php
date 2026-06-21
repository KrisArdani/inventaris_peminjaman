<?php
session_start();
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'kepala'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$role = $_SESSION['role'];

require '../koneksi.php';

// Pastikan folder assets/images/barang ada
if (!is_dir('../assets/images/barang')) {
    mkdir('../assets/images/barang', 0777, true);
}

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
            $time_filter = $_GET['time_filter'] ?? 'all_time';
            
            if ($time_filter === 'bulan_ini') {
                $total = $koneksi->query("SELECT COUNT(*) FROM barang WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();
            } else {
                $total = $koneksi->query("SELECT COUNT(*) FROM barang")->fetchColumn();
            }
            
            $tersedia  = $koneksi->query("SELECT SUM(stok_tersedia) FROM barang")->fetchColumn();
            $dipinjam  = $koneksi->query("SELECT SUM(stok_total - stok_tersedia) FROM barang")->fetchColumn();
            $habis     = $koneksi->query("SELECT COUNT(*) FROM barang WHERE stok_tersedia = 0")->fetchColumn();
            $pinjaman_aktif = $koneksi->query("SELECT COUNT(DISTINCT id_peminjaman) FROM peminjaman_detail WHERE status_item IN ('dipinjam', 'terlambat')")->fetchColumn();

            echo json_encode([
                'success'   => true,
                'total'     => (int)$total,
                'tersedia'  => (int)$tersedia,
                'dipinjam'  => (int)$dipinjam,
                'habis'     => (int)$habis,
                'pinjaman_aktif' => (int)$pinjaman_aktif,
            ]);
            break;

        // ---- CHART DATA ----
        case 'chart_data':
            // 1. Tren Peminjaman 6 bulan terakhir
            $sqlTren = "SELECT DATE_FORMAT(tgl_pengajuan, '%Y-%m') as bulan, COUNT(*) as jumlah
                        FROM peminjaman
                        GROUP BY DATE_FORMAT(tgl_pengajuan, '%Y-%m')
                        ORDER BY DATE_FORMAT(tgl_pengajuan, '%Y-%m') ASC
                        LIMIT 6";
            $stmtTren = $koneksi->query($sqlTren);
            $tren = $stmtTren->fetchAll(PDO::FETCH_ASSOC);

            // 2. Top 5 Barang terpopuler
            $sqlTop = "SELECT b.nama_barang, COUNT(d.id_detail) as total_dipinjam
                       FROM peminjaman_detail d
                       JOIN barang b ON d.id_barang = b.id_barang
                       GROUP BY d.id_barang
                       ORDER BY total_dipinjam DESC
                       LIMIT 5";
            $stmtTop = $koneksi->query($sqlTop);
            $top = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

            // 3. Distribusi Kategori Barang
            $sqlKat = "SELECT kategori, COUNT(*) as jumlah 
                       FROM barang 
                       GROUP BY kategori";
            $stmtKat = $koneksi->query($sqlKat);
            $kat = $stmtKat->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success'  => true,
                'tren'     => $tren,
                'top'      => $top,
                'kategori' => $kat
            ]);
            break;

        // ---- CREATE ----
        case 'create':
            if ($role !== 'admin') throw new Exception('Akses ditolak');
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $nama = $_POST['nama_barang'] ?? '';
            $kat  = $_POST['kategori'] ?? '';
            $stok = intval($_POST['stok_total'] ?? 0);
            $ters = intval($_POST['stok_tersedia'] ?? 0);
            $lok  = $_POST['lokasi'] ?? '';

            if (!$nama || !$kat) throw new Exception('Data tidak lengkap');
            if ($ters > $stok) throw new Exception('Stok tersedia tidak boleh melebihi stok total');

            // Generate ID otomatis
            $stmtLast = $koneksi->query("SELECT id_barang FROM barang ORDER BY id_barang DESC LIMIT 1");
            $lastId = $stmtLast->fetchColumn();
            if ($lastId) {
                // Asumsi format 'BRG-001'
                $num = intval(substr($lastId, 4)) + 1;
                $id = 'BRG-' . str_pad($num, 3, '0', STR_PAD_LEFT);
            } else {
                $id = 'BRG-001';
            }

            // Cek duplikasi ID (seharusnya aman karena auto-generated, tapi jaga-jaga)
            $chk = $koneksi->prepare("SELECT COUNT(*) FROM barang WHERE id_barang = ?");
            $chk->execute([$id]);
            if ($chk->fetchColumn() > 0) throw new Exception('Kode barang sudah digunakan, coba lagi.');

            $foto = null;
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $filename = $id . '_' . time() . '.' . $ext;
                $fotoPath = '../assets/images/barang/' . $filename;
                move_uploaded_file($_FILES['gambar']['tmp_name'], $fotoPath);
                $foto = $fotoPath;
            }

            $stmt = $koneksi->prepare("INSERT INTO barang (id_barang, nama_barang, kategori, stok_total, stok_tersedia, lokasi, gambar) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$id, $nama, $kat, $stok, $ters, $lok, $foto]);

            echo json_encode(['success' => true, 'message' => 'Barang berhasil ditambahkan', 'id_barang' => $id]);
            break;

        // ---- UPDATE ----
        case 'update':
            if ($role !== 'admin') throw new Exception('Akses ditolak');
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $id   = $_POST['id_barang'] ?? '';
            $nama = $_POST['nama_barang'] ?? '';
            $kat  = $_POST['kategori'] ?? '';
            $stok = intval($_POST['stok_total'] ?? 0);
            $ters = intval($_POST['stok_tersedia'] ?? 0);
            $lok  = $_POST['lokasi'] ?? '';

            if (!$id || !$nama || !$kat) throw new Exception('Data tidak lengkap');
            if ($ters > $stok) throw new Exception('Stok tersedia tidak boleh melebihi stok total');

            $fotoQuery = "";
            $params = [$nama, $kat, $stok, $ters, $lok];

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $filename = $id . '_' . time() . '.' . $ext;
                $fotoPath = '../assets/images/barang/' . $filename;
                move_uploaded_file($_FILES['gambar']['tmp_name'], $fotoPath);
                $fotoQuery = ", gambar=?";
                $params[] = $fotoPath;
            }

            $params[] = $id;

            $stmt = $koneksi->prepare("UPDATE barang SET nama_barang=?, kategori=?, stok_total=?, stok_tersedia=?, lokasi=? $fotoQuery WHERE id_barang=?");
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Barang berhasil diperbarui']);
            break;

        // ---- DELETE ----
        case 'delete':
            if ($role !== 'admin') throw new Exception('Akses ditolak');
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
