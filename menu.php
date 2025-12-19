<?php include 'config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menu - Warung Sederhana</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2 style="text-align:center; margin-bottom:40px; color:#5d7c1d;">Semua Menu Kami</h2>
        <div class="menu-grid">
            <?php
            $search = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
            $query = mysqli_query($koneksi, "SELECT * FROM menu WHERE nama_menu LIKE '%$search%'");
            while($row = mysqli_fetch_array($query)){
                $foto_clean = basename($row['gambar_path']);
            ?>
            <div class="card">
                <img src="images/<?php echo $foto_clean; ?>">
                <h3><?php echo $row['nama_menu']; ?></h3>
                <span class="price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                <a href="beli.php?id=<?php echo $row['id_menu']; ?>" class="btn-beli">Beli Sekarang</a>
            </div>
            <?php } ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>