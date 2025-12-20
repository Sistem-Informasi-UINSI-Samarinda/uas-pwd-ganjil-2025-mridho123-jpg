<?php
session_start();
require_once '../config/koneksi.php';

// 1. Proteksi Halaman
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$action = $_GET['action'] ?? 'read';

// Variabel default
$menu_data = [
    'id_menu' => '',
    'nama_menu' => '',
    'deskripsi' => '',
    'harga' => '',
    'kategori' => '',
    'gambar_path' => 'no_image.jpg',
    'status_tersedia' => 1
];

$kategori_list = ['Makanan Utama', 'Minuman Segar', 'Camilan', 'Paket Hemat'];

// --- FUNGSI UTAMA CRUD ---

// A. READ (Menampilkan Daftar Menu)
if ($action == 'read') {
    $query = "SELECT * FROM menu ORDER BY kategori, nama_menu ASC";
    $result = $koneksi->query($query);
    $menus = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $menus[] = $row;
        }
    }
}

// B. CREATE / UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['tambah_menu']) || isset($_POST['ubah_menu']))) {
    
    $id_menu = $koneksi->real_escape_string($_POST['id_menu'] ?? '');
    $nama_menu = $koneksi->real_escape_string(trim($_POST['nama_menu']));
    $deskripsi = $koneksi->real_escape_string(trim($_POST['deskripsi']));
    $harga = $koneksi->real_escape_string(str_replace('.', '', trim($_POST['harga'])));
    $kategori = $koneksi->real_escape_string($_POST['kategori']);
    $status_tersedia = isset($_POST['status_tersedia']) ? 1 : 0;
    $gambar_lama = $koneksi->real_escape_string($_POST['gambar_lama'] ?? 'no_image.jpg');
    $gambar_path = $gambar_lama;

    // --- VALIDASI DUPLIKAT NAMA ---
    $cek_sql = "SELECT id_menu FROM menu WHERE nama_menu = '$nama_menu'";
    if (isset($_POST['ubah_menu'])) {
        $cek_sql .= " AND id_menu != '$id_menu'"; // Abaikan diri sendiri saat update
    }
    $cek_res = $koneksi->query($cek_sql);

    if ($cek_res->num_rows > 0) {
        $message = '<div class="alert alert-error">Gagal! Nama menu *' . htmlspecialchars($nama_menu) . '* sudah digunakan.</div>';
    } else {
        // Logika Upload Gambar
        if (isset($_FILES['gambar_baru']) && $_FILES['gambar_baru']['error'] == 0) {
            $target_dir = "../images/";
            $file_extension = strtolower(pathinfo($_FILES["gambar_baru"]["name"], PATHINFO_EXTENSION));
            $new_file_name = strtolower(str_replace(' ', '', $nama_menu)) . '' . time() . '.' . $file_extension;
            
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($_FILES["gambar_baru"]["tmp_name"], $target_dir . $new_file_name)) {
                    $gambar_path = $new_file_name;
                    if (isset($_POST['ubah_menu']) && $gambar_lama != 'no_image.jpg' && file_exists($target_dir . $gambar_lama)) {
                        unlink($target_dir . $gambar_lama);
                    }
                }
            }
        }

        // Simpan ke Database
        $stmt = null; // Inisialisasi awal
        if (isset($_POST['tambah_menu'])) {
            $stmt = $koneksi->prepare("INSERT INTO menu (nama_menu, deskripsi, harga, kategori, gambar_path, status_tersedia) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $nama_menu, $deskripsi, $harga, $kategori, $gambar_path, $status_tersedia);
        } else {
            $stmt = $koneksi->prepare("UPDATE menu SET nama_menu=?, deskripsi=?, harga=?, kategori=?, gambar_path=?, status_tersedia=? WHERE id_menu=?");
            $stmt->bind_param("sssssii", $nama_menu, $deskripsi, $harga, $kategori, $gambar_path, $status_tersedia, $id_menu);
        }

        if ($stmt && $stmt->execute()) {
            $message = '<div class="alert alert-success">Data berhasil disimpan!</div>';
            $action = 'read';
            // Refresh data setelah update
            header("Location: menu_crud.php?msg=success");
            exit;
        } else {
            $message = '<div class="alert alert-error">Gagal menyimpan: ' . $koneksi->error . '</div>';
        }
        
        if ($stmt) $stmt->close(); // Tutup hanya jika variabel stmt ada
    }
}

// C. DELETE
if ($action == 'delete' && isset($_GET['id'])) {
    $id_menu = $koneksi->real_escape_string($_GET['id']);
    $stmt = $koneksi->prepare("DELETE FROM menu WHERE id_menu = ?");
    $stmt->bind_param("i", $id_menu);
    if ($stmt->execute()) {
        header('Location: menu_crud.php?msg=deleted');
    } else {
        header('Location: menu_crud.php?msg=error');
    }
    $stmt->close();
    exit;
}

// D. READ Detail untuk Edit
if ($action == 'update' && isset($_GET['id'])) {
    $id_menu = $koneksi->real_escape_string($_GET['id']);
    $res = $koneksi->query("SELECT * FROM menu WHERE id_menu = '$id_menu'");
    if ($res->num_rows == 1) $menu_data = $res->fetch_assoc();
}

// Pesan dari Redirect
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'success') $message = '<div class="alert alert-success">Berhasil disimpan!</div>';
    if ($_GET['msg'] == 'deleted') $message = '<div class="alert alert-success">Berhasil dihapus!</div>';
    if ($_GET['msg'] == 'error') $message = '<div class="alert alert-error">Gagal! Menu terikat data pesanan.</div>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Menu</title>
    <link rel="stylesheet" href="../css/style2.css">
</head>
<body class="admin-body">
    <div class="sidebar">
        <h2 class="logo-admin">Admin</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="menu_crud.php" class="active">Kelola Menu</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <h1>Manajemen Menu</h1>
        <?= $message; ?>

        <?php if ($action == 'read'): ?>
            <a href="menu_crud.php?action=create" class="btn btn-primary">Tambah Menu</a>
            <table class="data-table">
                <thead>
                    <tr><th>Gambar</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $query = "SELECT * FROM menu ORDER BY id_menu DESC";
                    $result = $koneksi->query($query);
                    while($menu = $result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="../images/<?= $menu['gambar_path']; ?>" width="50"></td>
                        <td><?= $menu['nama_menu']; ?></td>
                        <td><?= $menu['kategori']; ?></td>
                        <td>Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="menu_crud.php?action=update&id=<?= $menu['id_menu']; ?>">Edit</a> | 
                            <a href="menu_crud.php?action=delete&id=<?= $menu['id_menu']; ?>" onclick="return confirm('Hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <form action="menu_crud.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_menu" value="<?= $menu_data['id_menu']; ?>">
                <input type="hidden" name="gambar_lama" value="<?= $menu_data['gambar_path']; ?>">
                
                <label>Nama Menu:</label><br>
                <input type="text" name="nama_menu" value="<?= $menu_data['nama_menu']; ?>" required><br><br>
                
                <label>Harga:</label><br>
                <input type="number" name="harga" value="<?= $menu_data['harga']; ?>" required><br><br>

                <label>Deskripsi:</label><br>
                <textarea name="deskripsi" rows="4" cols="50"><?= $menu_data['deskripsi']; ?></textarea><br><br>

                <label>Kategori:</label><br>
                <select name="kategori">
                    <?php foreach($kategori_list as $k): ?>
                        <option value="<?= $k ?>" <?= ($menu_data['kategori'] == $k) ? 'selected' : '' ?>><?= $k ?></option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Status Tersedia:</label><br>
                <input type="checkbox" name="status_tersedia" value="1" <?= ($menu_data['status_tersedia'] == 1) ? 'checked' : '' ?>> Tersedia<br><br>

                <label>Gambar:</label><br>
                <input type="file" name="gambar_baru"><br><br>

                <button type="submit" name="<?= ($action == 'create') ? 'tambah_menu' : 'ubah_menu' ?>">Simpan</button>
                <a href="menu_crud.php">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>