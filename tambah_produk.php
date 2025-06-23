<?php
session_start();
include 'includes/koneksi.php'; // Pastikan koneksi benar

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = $_POST['nama_produk'];
    $merek     = $_POST['merek'];
    $kategori  = $_POST['kategori'];
    $harga     = $_POST['harga'];
    $stok      = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'];
    $gambar    = '';

    // Proses upload gambar
    if (!empty($_FILES['gambar']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir);
        }

        $fileName = time() . '_' . basename($_FILES["gambar"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $targetFile)) {
            $gambar = $fileName; // hanya nama file, bukan path lengkap
        }
    }

    // Simpan data ke database
    $stmt = $koneksi->prepare("INSERT INTO produk (nama_produk, merek, kategori, harga, stok, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdiss", $nama, $merek, $kategori, $harga, $stok, $deskripsi, $gambar);
    $stmt->execute();
    $stmt->close();

    // Kirim notifikasi & redirect
    $_SESSION['notifikasi'] = "Produk telah ditambahkan!";
    header("Location: pengguna_dashboard.php");
    exit;
}
?>
