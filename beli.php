<?php 
include 'config/koneksi.php'; 

$id_menu = $_GET['id'];
$query_menu = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_menu = '$id_menu'");
$data_menu = mysqli_fetch_array($query_menu);

if(isset($_POST['konfirmasi'])){
    $nama = $_POST['nama_pelanggan'];
    $hp = $_POST['nomor_hp'];
    $metode = $_POST['metode_bayar'];
    $tgl = date('Y-m-d H:i:s');
    $jumlah = $_POST['jumlah'];

    $insert_pesanan = mysqli_query($koneksi, "INSERT INTO pesanan (nama_pelanggan, nomor_hp, tanggal_pesan, metode_bayar, status) 
                      VALUES ('$nama', '$hp', '$tgl', '$metode', 'Menunggu Konfirmasi')");
    
    if($insert_pesanan){
        $id_pesanan_baru = mysqli_insert_id($koneksi);
        mysqli_query($koneksi, "INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah) 
                     VALUES ('$id_pesanan_baru', '$id_menu', '$jumlah')");
        echo "<script>alert('Pesanan Berhasil!'); window.location='pesanan_saya.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beli - Warung Sederhana</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2 style="color:#5d7c1d;">Konfirmasi Pembelian</h2>
        <div style="display: flex; gap: 20px; background:white; padding:30px; border-radius:15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="flex: 1; border-right: 1px solid #eee; padding-right: 20px;">
                <img src="images/<?php echo basename($data_menu['gambar_path']); ?>" style="width:100%; border-radius:10px;">
                <h4><?php echo $data_menu['nama_menu']; ?></h4>
                <p>Harga: <strong>Rp <?php echo number_format($data_menu['harga'], 0, ',', '.'); ?></strong></p>
            </div>
            <div style="flex: 2;">
                <form action="" method="POST">
                    <input type="text" name="nama_pelanggan" placeholder="Nama Lengkap" required style="width:100%; padding:10px; margin-bottom:15px;">
                    <input type="number" name="nomor_hp" placeholder="Nomor HP/WA" required style="width:100%; padding:10px; margin-bottom:15px;">
                    <input type="number" name="jumlah" value="1" min="1" required style="width:100%; padding:10px; margin-bottom:15px;">
                    <select name="metode_bayar" required style="width:100%; padding:10px; margin-bottom:15px;">
                        <option value="Tunai">Tunai</option>
                        <option value="DANA">DANA</option>
                        <option value="OVO">OVO</option>
                        <option value="PayPal">PayPal</option>
                    </select>
                    <button type="submit" name="konfirmasi" class="btn-beli" style="width:100%; border:none; cursor:pointer; padding:15px;">Konfirmasi Pesanan</button>
                </form>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>