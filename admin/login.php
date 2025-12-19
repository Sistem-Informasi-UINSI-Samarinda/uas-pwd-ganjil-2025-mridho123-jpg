<?php
session_start();
// Cek apakah admin sudah login, jika ya, arahkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Sertakan koneksi database
require_once '../config/koneksi.php';

$error_message = '';

// Logika Pemrosesan Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $koneksi->real_escape_string(trim($_POST['username']));
    $password_input = trim($_POST['password']);

    // 1. Query untuk mengambil data admin berdasarkan username
    $query = "SELECT id_admin, username, password, nama_lengkap FROM admin WHERE username = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $username_input);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // 2. Verifikasi Password (Menggunakan password_verify untuk hash)
        // Saat ini, kita akan simulasikan dengan plain text untuk kemudahan uji coba,
        // TAPI HARUS DIGANTI DENGAN password_verify($password_input, $admin['password'])
        
        // Contoh Penggunaan Hashing yang Benar:
        if (password_verify($password_input, $admin['password'])) {
            // Jika berhasil login, buat session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];

            // Arahkan ke dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error_message = "Username atau password salah.";
        }
        
    } else {
        $error_message = "Username atau password salah.";
    }

    $stmt->close();
}

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Warung Sederhana</title>
    <link rel="stylesheet" href="../css/style2.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="admin-login-body">

    <div class="login-container">
        <h2 class="login-header">Login Admin</h2>
        <p class="login-subtitle">Masukkan kredensial Anda untuk mengakses dashboard.</p>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full-width">LOGIN</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">← Kembali ke Beranda Warung</a>
        </div>
    </div>

</body>
</html>