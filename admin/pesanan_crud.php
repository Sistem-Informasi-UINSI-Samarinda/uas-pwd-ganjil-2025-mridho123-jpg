<?php
session_start();
require_once '../config/koneksi.php';

// 1. Proteksi Halaman
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$action = $_GET['action'] ?? 'read'; // Default action adalah melihat daftar (read)
$filter_status = $_GET['status'] ?? 'Menunggu Konfirmasi'; // Default filter

// --- FUNGSI UTAMA CRUD ---

// A. READ (Menampilkan Daftar Pesanan)
if ($action == 'read') {
    // Query dasar untuk mengambil data pesanan utama
    $query = "SELECT 
                id_pesanan, nama_pelanggan, nomor_hp, tanggal_pesan, metode_bayar, status
              FROM pesanan 
              WHERE status = ? 
              ORDER BY tanggal_pesan DESC";
    
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $filter_status);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// B. READ DETAIL (Melihat Isi Pesanan)
if ($action == 'detail' && isset($_GET['id'])) {
    $id_pesanan = $koneksi->real_escape_string($_GET['id']);
    
    // Query 1: Ambil data header pesanan
    $query_header = "SELECT * FROM pesanan WHERE id_pesanan = ?";
    $stmt_header = $koneksi->prepare($query_header);
    $stmt_header->bind_param("i", $id_pesanan);
    $stmt_header->execute();
    $order_header = $stmt_header->get_result()->fetch_assoc();
    $stmt_header->close();

    // Query 2: Ambil detail item pesanan menggunakan JOIN
    $query_detail = "SELECT 
                        dp.jumlah, dp.catatan_khusus, 
                        m.nama_menu, m.harga 
                     FROM detail_pesanan dp
                     JOIN menu m ON dp.id_menu = m.id_menu
                     WHERE dp.id_pesanan = ?";
    $stmt_detail = $koneksi->prepare($query_detail);
    $stmt_detail->bind_param("i", $id_pesanan);
    $stmt_detail->execute();
    $order_details = $stmt_detail->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_detail->close();

    // Hitung Total Belanja
    $total_harga = 0;
    foreach ($order_details as $item) {
        $total_harga += $item['harga'] * $item['jumlah'];
    }
}

// C. UPDATE STATUS (Mengubah Status Pesanan)
if ($action == 'update_status' && isset($_GET['id']) && isset($_GET['new_status'])) {
    $id_pesanan = $koneksi->real_escape_string($_GET['id']);
    $new_status = $koneksi->real_escape_string($_GET['new_status']);

    $valid_statuses = ['Menunggu Konfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'];

    if (in_array($new_status, $valid_statuses)) {
        $stmt = $koneksi->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
        $stmt->bind_param("si", $new_status, $id_pesanan);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Status pesanan **#' . $id_pesanan . '** berhasil diperbarui menjadi **' . $new_status . '**.</div>';
        } else {
            $message = '<div class="alert alert-error">Gagal memperbarui status: ' . $koneksi->error . '</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="alert alert-error">Status tidak valid.</div>';
    }
    // Redirect kembali ke halaman read setelah update
    header('Location: pesanan_crud.php?status=' . urlencode($filter_status) . '&message=' . urlencode($message));
    exit;
}

// Menampilkan pesan dari redirect
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Warung Sederhana</title>
    <link rel="stylesheet" href="../css/style2.css">
</head>
<body class="admin-body">

    <div class="sidebar">
        <h2 class="logo-admin">Admin Panel</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="pesanan_crud.php" class="active">Kelola Pesanan</a></li>
                <li><a href="menu_crud.php">Kelola Menu</a></li>
                <li class="separator"></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <header class="admin-header">
            <h1>Kelola Pesanan Pelanggan</h1>
        </header>

        <?= $message; ?>

        <?php if ($action == 'read'): ?>
            <section class="crud-list-section">
                <div class="filter-controls">
                    <label>Filter Status:</label>
                    <?php 
                        $statuses = ['Menunggu Konfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'];
                        foreach ($statuses as $s) {
                            $is_active = ($filter_status == $s) ? 'active' : '';
                            echo '<a href="pesanan_crud.php?status=' . urlencode($s) . '" class="btn btn-filter ' . $is_active . '">' . $s . '</a>';
                        }
                    ?>
                </div>

                <div class="crud-header">
                    <h2>Daftar Pesanan (Status: <?= htmlspecialchars($filter_status); ?>)</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table order-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Waktu Pesan</th>
                                <th>Pelanggan</th>
                                <th>HP</th>
                                <th>Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="7" class="text-center">Tidak ada pesanan dengan status **<?= htmlspecialchars($filter_status); ?>**.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($order['id_pesanan']); ?></td>
                                        <td><?= date('d M H:i', strtotime($order['tanggal_pesan'])); ?></td>
                                        <td><?= htmlspecialchars($order['nama_pelanggan']); ?></td>
                                        <td><?= htmlspecialchars($order['nomor_hp']); ?></td>
                                        <td><?= htmlspecialchars($order['metode_bayar']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                                <?= htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="pesanan_crud.php?action=detail&id=<?= $order['id_pesanan']; ?>" class="action-link detail-link">Detail</a>
                                            
                                            <?php if ($order['status'] == 'Menunggu Konfirmasi'): ?>
                                                | <a href="pesanan_crud.php?action=update_status&id=<?= $order['id_pesanan']; ?>&new_status=Diproses" class="action-link process-link">Proses</a>
                                            <?php elseif ($order['status'] == 'Diproses'): ?>
                                                | <a href="pesanan_crud.php?action=update_status&id=<?= $order['id_pesanan']; ?>&new_status=Selesai" class="action-link finish-link">Selesai</a>
                                            <?php endif; ?>

                                            <?php if ($order['status'] != 'Dibatalkan' && $order['status'] != 'Selesai'): ?>
                                                | <a href="pesanan_crud.php?action=update_status&id=<?= $order['id_pesanan']; ?>&new_status=Dibatalkan" onclick="return confirm('Yakin batalkan pesanan #<?= $order['id_pesanan']; ?>?')" class="action-link delete-link">Batal</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($action == 'detail'): ?>
            <?php if (!empty($order_header)): ?>
                <section class="order-detail-section">
                    <h2>Detail Pesanan #<?= htmlspecialchars($order_header['id_pesanan']); ?></h2>
                    
                    <div class="detail-grid">
                        <div class="detail-card">
                            <h3>Info Pelanggan</h3>
                            <p><strong>Nama:</strong> <?= htmlspecialchars($order_header['nama_pelanggan']); ?></p>
                            <p><strong>HP:</strong> <?= htmlspecialchars($order_header['nomor_hp']); ?></p>
                            <p><strong>Metode Bayar:</strong> <?= htmlspecialchars($order_header['metode_bayar']); ?></p>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Status & Waktu</h3>
                            <p><strong>Waktu Pesan:</strong> <?= date('d F Y H:i', strtotime($order_header['tanggal_pesan'])); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order_header['status'])); ?>">
                                    <?= htmlspecialchars($order_header['status']); ?>
                                </span>
                            </p>
                            <div class="detail-action-buttons">
                                <a href="pesanan_crud.php?action=update_status&id=<?= $order_header['id_pesanan']; ?>&new_status=Diproses" class="btn btn-secondary">Ubah ke Diproses</a>
                                <a href="pesanan_crud.php?action=update_status&id=<?= $order_header['id_pesanan']; ?>&new_status=Selesai" class="btn btn-primary">Ubah ke Selesai</a>
                            </div>
                        </div>
                    </div>

                    <h3>Item Pesanan</h3>
                    <div class="table-responsive">
                        <table class="data-table item-table">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_details as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['nama_menu']); ?></td>
                                        <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                        <td><?= htmlspecialchars($item['jumlah']); ?></td>
                                        <td><?= empty($item['catatan_khusus']) ? '-' : htmlspecialchars($item['catatan_khusus']); ?></td>
                                        <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Total Pembayaran</strong></td>
                                    <td><strong>Rp <?= number_format($total_harga, 0, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="form-action" style="text-align: left;">
                        <a href="pesanan_crud.php" class="btn btn-secondary">← Kembali ke Daftar Pesanan</a>
                    </div>
                </section>
            <?php else: ?>
                <div class="alert alert-error">Data pesanan tidak ditemukan.</div>
                <div class="form-action"><a href="pesanan_crud.php" class="btn btn-secondary">← Kembali ke Daftar</a></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</body>
</html>