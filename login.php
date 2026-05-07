<?php
session_start();
require 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password_hash'])) {
                    // Password matches, set sessions
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] == 'admin') {
                        header("Location: admin/index.php");
                        exit;
                    } elseif ($user['role'] == 'kepala') {
                        header("Location: kepala/index.php");
                        exit;
                    } elseif ($user['role'] == 'anggota') {
                        header("Location: anggota/index.php");
                        exit;
                    }
                } else {
                    $error = "Password yang Anda masukkan salah.";
                }
            } else {
                $error = "Username tidak ditemukan.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    } else {
        $error = "Silakan isi username dan password.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Inventaris - BEM Politeknik Purbaya</title>
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('assets/images/hero_bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            overflow: hidden;
        }

        .login-overlay {
            position: absolute;
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
            max-width: 450px;
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
            width: 60px;
            height: 60px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 10px rgba(56, 102, 65, 0.3);
        }

        .login-header h2 {
            font-size: 1.75rem;
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

        .form-group {
            margin-bottom: 1.5rem;
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
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 1rem;
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
            padding: 1rem;
            font-size: 1.1rem;
            margin-top: 1rem;
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
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <h2>Sistem Inventaris</h2>
                <p>Silakan login untuk mengakses layanan peminjaman barang BEM Politeknik Purbaya.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username / Email</label>
                    <div class="input-group">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    Login <i class="fa-solid fa-right-to-bracket"></i>
                </button>
                <div style="text-align: center; margin-top: 1rem; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">Belum punya akun?</span> 
                    <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Daftar di sini</a>
                </div>
            </form>

            <a href="index.html" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

</body>
</html>
