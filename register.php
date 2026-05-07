<?php
session_start();
require 'koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username     = trim($_POST['username']);
    $password     = $_POST['password'];
    $no_hp        = trim($_POST['no_hp']);
    $asal_ormawa  = trim($_POST['asal_ormawa']);

    if (!empty($nama_lengkap) && !empty($username) && !empty($password)) {
        try {
            // Check if username already exists
            $stmt = $koneksi->prepare("SELECT id_user FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->fetch()) {
                $error = "Username sudah digunakan. Silakan gunakan username lain.";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $role = 'anggota'; // Default role for registration

                $insert = $koneksi->prepare("INSERT INTO users (username, password_hash, nama_lengkap, role, no_hp, asal_ormawa) VALUES (:username, :password, :nama_lengkap, :role, :no_hp, :asal_ormawa)");
                
                $insert->bindParam(':username', $username);
                $insert->bindParam(':password', $hashed_password);
                $insert->bindParam(':nama_lengkap', $nama_lengkap);
                $insert->bindParam(':role', $role);
                $insert->bindParam(':no_hp', $no_hp);
                $insert->bindParam(':asal_ormawa', $asal_ormawa);

                if ($insert->execute()) {
                    $success = "Pendaftaran berhasil! Silakan login.";
                } else {
                    $error = "Pendaftaran gagal. Silakan coba lagi.";
                }
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi semua field yang wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Anggota - BEM Politeknik Purbaya</title>
    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('assets/images/hero_bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 2rem 0;
        }

        .login-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(56, 102, 65, 0.9), rgba(17, 24, 39, 0.8));
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            padding: 0 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 3rem 2.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            width: 50px;
            height: 50px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 10px rgba(56, 102, 65, 0.3);
        }

        .login-header h2 {
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
            border: 1px solid #f87171;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
            border: 1px solid #4ade80;
        }

        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-main);
            font-size: 0.9rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            color: var(--text-muted);
            transition: var(--transition-fast);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 0.95rem;
            color: var(--text-main);
            background-color: var(--bg-color);
            transition: var(--transition-fast);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(56, 102, 65, 0.1);
            background-color: white;
        }

        .form-control:focus + i {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            font-size: 1.05rem;
            margin-top: 0.5rem;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition-fast);
        }

        .back-link:hover {
            color: var(--primary);
        }

        .back-link i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>

    <div class="login-overlay"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h2>Registrasi Anggota</h2>
                <p>Bergabung dan manfaatkan fasilitas BEM Politeknik Purbaya.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                    <br>
                    <a href="login.php" style="color: #166534; font-weight: bold; text-decoration: underline; display: block; margin-top: 10px;">Klik di sini untuk Login</a>
                </div>
            <?php else: ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap <span style="color: red;">*</span></label>
                    <div class="input-group">
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required autofocus>
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username <span style="color: red;">*</span></label>
                    <div class="input-group">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Pilih username" required>
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password <span style="color: red;">*</span></label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Buat password" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="no_hp">No. HP / WhatsApp</label>
                    <div class="input-group">
                        <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="08xx...">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="asal_ormawa">Asal Ormawa / Jurusan</label>
                    <div class="input-group">
                        <input type="text" id="asal_ormawa" name="asal_ormawa" class="form-control" placeholder="Contoh: HIMA TI">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    Daftar Sekarang <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
            
            <?php endif; ?>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                <span style="color: var(--text-muted);">Sudah punya akun?</span> 
                <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Login di sini</a>
            </div>

            <a href="index.html" class="back-link" style="margin-top: 1rem;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

</body>
</html>
