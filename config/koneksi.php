<?php
// Konfigurasi Database
$host = "localhost"; // Biasanya localhost
$user = "root";      // Username default XAMPP/WAMP
$pass = "";          // Password default XAMPP/WAMP (kosong)
$db = "warung_sederhana"; // Nama database yang baru Anda buat

// Membuat koneksi ke database menggunakan MySQLi
$koneksi = new mysqli($host, $user, $pass, $db);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    // Tampilkan pesan error dan hentikan eksekusi skrip jika gagal
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Opsional: Set karakter set menjadi UTF8
$koneksi->set_charset("utf8mb4");

// Koneksi berhasil, variabel $koneksi siap digunakan di halaman lain
?>