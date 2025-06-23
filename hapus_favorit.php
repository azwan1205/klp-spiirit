<?php
session_start();
include 'includes/koneksi.php';

if (!isset($_SESSION['id'])) {
  header("Location: index.html");
  exit;
}

$id_user = $_SESSION['id'];
$id_produk = $_POST['id_produk'] ?? 0;

// Hapus dari tabel favorit
mysqli_query($koneksi, "DELETE FROM favorit WHERE id_user = $id_user AND id_produk = $id_produk");

// Tambahkan notifikasi (opsional)
$_SESSION['notif'] = "✅ Produk telah dihapus dari favorit.";

// Redirect kembali ke halaman dashboard favorit
header("Location: user_dashboard.php?page=favorit");
exit;
