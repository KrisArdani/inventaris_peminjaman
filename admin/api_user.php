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
        case 'list':
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? 'semua';
            $page = intval($_GET['page'] ?? 1);
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $where = "1=1";
            $params = [];

            if ($search) {
                $where .= " AND (nama_lengkap LIKE ? OR username LIKE ? OR asal_ormawa LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if ($role !== 'semua') {
                $where .= " AND role = ?";
                $params[] = $role;
            }

            // Get total for pagination
            $stmtCount = $koneksi->prepare("SELECT COUNT(*) FROM users WHERE $where");
            $stmtCount->execute($params);
            $total = $stmtCount->fetchColumn();

            // Get data
            $sql = "SELECT id_user, username, nama_lengkap, role, no_hp, asal_ormawa, created_at 
                    FROM users 
                    WHERE $where 
                    ORDER BY created_at DESC 
                    LIMIT $limit OFFSET $offset";
            $stmt = $koneksi->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
            break;

        case 'create':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);

            $nama = trim($data['nama_lengkap'] ?? '');
            $user = trim($data['username'] ?? '');
            $pass = $data['password'] ?? '';
            $role = $data['role'] ?? '';
            $nohp = trim($data['no_hp'] ?? '');
            $ormawa = trim($data['asal_ormawa'] ?? '');

            if (!$nama || !$user || !$pass || !$role) {
                throw new Exception('Data tidak lengkap (Nama, Username, Password, Role wajib diisi)');
            }

            // Check username duplicate
            $cek = $koneksi->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $cek->execute([$user]);
            if ($cek->fetchColumn() > 0) throw new Exception('Username sudah digunakan');

            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $id = 'USR-' . bin2hex(random_bytes(4)); // or just UUID, but let's use UUID function from MySQL if possible, wait, schema says UUID() default.
            // Let's rely on default uuid() by not inserting id_user, but MySQL default uuid() might only work if we skip it?
            // Let's just generate a UUID here to be safe.
            $id_user = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            $stmt = $koneksi->prepare("INSERT INTO users (id_user, username, password_hash, nama_lengkap, role, no_hp, asal_ormawa) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_user, $user, $hashed, $nama, $role, $nohp, $ormawa]);

            echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan']);
            break;

        case 'update':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);

            $id = $data['id_user'] ?? '';
            $nama = trim($data['nama_lengkap'] ?? '');
            $user = trim($data['username'] ?? '');
            $pass = $data['password'] ?? ''; // Optional
            $role = $data['role'] ?? '';
            $nohp = trim($data['no_hp'] ?? '');
            $ormawa = trim($data['asal_ormawa'] ?? '');

            if (!$id || !$nama || !$user || !$role) {
                throw new Exception('Data tidak lengkap');
            }

            // Check username duplicate for OTHER users
            $cek = $koneksi->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id_user != ?");
            $cek->execute([$user, $id]);
            if ($cek->fetchColumn() > 0) throw new Exception('Username sudah digunakan oleh user lain');

            if ($pass) {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $koneksi->prepare("UPDATE users SET username=?, password_hash=?, nama_lengkap=?, role=?, no_hp=?, asal_ormawa=? WHERE id_user=?");
                $stmt->execute([$user, $hashed, $nama, $role, $nohp, $ormawa, $id]);
            } else {
                $stmt = $koneksi->prepare("UPDATE users SET username=?, nama_lengkap=?, role=?, no_hp=?, asal_ormawa=? WHERE id_user=?");
                $stmt->execute([$user, $nama, $role, $nohp, $ormawa, $id]);
            }

            echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);
            break;

        case 'delete':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id_user'] ?? '';

            if (!$id) throw new Exception('ID User tidak valid');
            if ($id === $_SESSION['user_id']) throw new Exception('Anda tidak bisa menghapus akun Anda sendiri');

            try {
                $stmt = $koneksi->prepare("DELETE FROM users WHERE id_user = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
                    throw new Exception('Gagal menghapus! User ini masih memiliki riwayat peminjaman atau data terkait.');
                }
                throw $e;
            }
            break;

        case 'stats':
            $time_filter = $_GET['time_filter'] ?? 'all_time';

            if ($time_filter === 'bulan_ini') {
                $total = $koneksi->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();
                $admin = $koneksi->query("SELECT COUNT(*) FROM users WHERE role='admin' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();
                $anggota = $koneksi->query("SELECT COUNT(*) FROM users WHERE role='anggota' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();
                $kepala = $koneksi->query("SELECT COUNT(*) FROM users WHERE role='kepala' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();
            } else {
                $total = $koneksi->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $admin = $koneksi->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
                $anggota = $koneksi->query("SELECT COUNT(*) FROM users WHERE role='anggota'")->fetchColumn();
                $kepala = $koneksi->query("SELECT COUNT(*) FROM users WHERE role='kepala'")->fetchColumn();
            }

            echo json_encode([
                'success' => true,
                'total' => $total,
                'admin' => $admin,
                'anggota' => $anggota,
                'kepala' => $kepala
            ]);
            break;

        default:
            throw new Exception('Action tidak valid');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
