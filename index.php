<?php include 'config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - Warung Sederhana</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="banner">
        <h1>Selamat Datang di Warung Sederhana</h1>
        <form action="menu.php" method="GET" class="search-box">
            <input type="text" name="cari" placeholder="Cari menu favorit Anda...">
            <button type="submit">Cari</button>
        </form>
    </div>

    <div class="container">
        <h2 style="text-align:center; margin-bottom:40px; color:#5d7c1d;">Menu Pilihan</h2>
        <div class="menu-grid">
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM menu LIMIT 6");
            while($row = mysqli_fetch_array($query)){
                $foto_clean = basename($row['gambar_path']); 
            ?>
            <div class="card">
                <img src="images/<?php echo $foto_clean; ?>" alt="menu">
                <h3><?php echo $row['nama_menu']; ?></h3>
                <span class="price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                <a href="beli.php?id=<?php echo $row['id_menu']; ?>" class="btn-beli">Beli</a>
            </div>
            <?php } ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>