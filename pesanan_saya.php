<?php include 'config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya - Warung Sederhana</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2 style="color:#5d7c1d;">Riwayat Pesanan</h2>
        <table>
            <thead>
                <tr>
                    <th>Tgl Pesan</th>
                    <th>Nama Menu</th>
                    <th>Nama Pembeli</th>
                    <th>Metode</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = mysqli_query($koneksi, "SELECT * FROM detail_pesanan 
                       JOIN pesanan ON detail_pesanan.id_pesanan = pesanan.id_pesanan
                       JOIN menu ON detail_pesanan.id_menu = menu.id_menu
                       ORDER BY pesanan.id_pesanan DESC");
                while($d = mysqli_fetch_array($sql)){
                ?>
                <tr>
                    <td><?php echo $d['tanggal_pesan']; ?></td>
                    <td><?php echo $d['nama_menu']; ?> (x<?php echo $d['jumlah']; ?>)</td>
                    <td><?php echo $d['nama_pelanggan']; ?></td>
                    <td><?php echo $d['metode_bayar']; ?></td>
                    <td style="color:#5d7c1d; font-weight:bold;"><?php echo $d['status']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>