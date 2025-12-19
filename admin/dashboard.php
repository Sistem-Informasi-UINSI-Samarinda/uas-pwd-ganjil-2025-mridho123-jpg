<?php
session_start();
// Sertakan koneksi database
require_once '../config/koneksi.php';

// 1. Proteksi Halaman: Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Jika belum login, arahkan kembali ke halaman login
    header('Location: login.php');
    exit;
}

// Ambil nama admin dari sesi untuk sapaan
$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator';

// 2. Mengambil Data Ringkasan (untuk Statistik Dashboard)
$stats = [
    'pesanan_baru' => 0,
    'total_menu' => 0,
    'total_pendapatan_selesai' => 0
];

// Query 1: Jumlah Pesanan Baru (status 'Menunggu Konfirmasi')
$query_baru = "SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Menunggu Konfirmasi'";
$result_baru = $koneksi->query($query_baru);
if ($result_baru && $row = $result_baru->fetch_assoc()) {
    $stats['pesanan_baru'] = $row['total'];
}

// Query 2: Total Menu Aktif
$query_menu = "SELECT COUNT(*) AS total FROM menu WHERE status_tersedia = 1";
$result_menu = $koneksi->query($query_menu);
if ($result_menu && $row = $result_menu->fetch_assoc()) {
    $stats['total_menu'] = $row['total'];
}

// Query 3: Total Pendapatan Pesanan Selesai (Simulasi, butuh join dengan detail_pesanan)
// Karena ini kompleks, kita akan simulasikan saja untuk demo dashboard:
// Dalam implementasi nyata, Anda akan menggunakan JOIN dan SUM.
$stats['total_pendapatan_selesai'] = 1250000; // Hardcode simulasi Rp 1.250.000

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Warung Sederhana</title>
    <link rel="stylesheet" href="../css/style2.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

    <div class="sidebar">
        <h2 class="logo-admin">Admin Panel</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="pesanan_crud.php">Kelola Pesanan</a></li>
                <li><a href="menu_crud.php">Kelola Menu</a></li>
                <li class="separator"></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <header class="admin-header">
            <h1>Selamat Datang, <?= htmlspecialchars($admin_nama); ?>!</h1>
        </header>

        <section class="dashboard-stats">
            <h2>Ringkasan Data</h2>
            <div class="stats-grid">
                
                <div class="stat-card stat-alert">
                    <h3>Pesanan Baru</h3>
                    <p class="stat-number"><?= $stats['pesanan_baru']; ?></p>
                    <a href="pesanan_crud.php" class="card-link">Lihat Detail →</a>
                </div>

                <div class="stat-card stat-info">
                    <h3>Menu Aktif</h3>
                    <p class="stat-number"><?= $stats['total_menu']; ?></p>
                    <a href="menu_crud.php" class="card-link">Kelola Menu →</a>
                </div>

                <div class="stat-card stat-success">
                    <h3>Total Pendapatan (Selesai)</h3>
                    <p class="stat-number">Rp <?= number_format($stats['total_pendapatan_selesai'], 0, ',', '.'); ?></p>
                    <span class="card-note">Simulasi Bulan Ini</span>
                </div>
            </div>
        </section>
        
        <section class="quick-actions">
            <h2>Aksi Cepat</h2>
            <div class="action-buttons">
                <a href="pesanan_crud.php?status=Menunggu Konfirmasi" class="btn btn-primary">Konfirmasi Pesanan Baru</a>
                <a href="menu_crud.php?action=add" class="btn btn-secondary">Tambah Menu Baru</a>
            </div>
        </section>
    </div>

</body>
</html>