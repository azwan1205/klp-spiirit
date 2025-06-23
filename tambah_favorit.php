<?php
session_start();
include 'includes/koneksi.php';

if (!isset($_SESSION['id'])) {
  header("Location: index.html");
  exit;
}

$id_user = $_SESSION['id'];
$id_produk = $_POST['id_produk'];
$redirect = $_POST['current_page'] ?? 'user_dashboard.php';

if ($id_user && $id_produk) {
  $cek = mysqli_query($koneksi, "SELECT * FROM favorit WHERE id_user=$id_user AND id_produk=$id_produk");
  if (mysqli_num_rows($cek) == 0) {
    mysqli_query($koneksi, "INSERT INTO favorit (id_user, id_produk) VALUES ($id_user, $id_produk)");
    $_SESSION['notif'] = "✅ Produk telah ditambahkan ke favorit.";
  } else {
    $_SESSION['notif'] = "⚠️ Produk sudah ada di favorit.";
  }
}

header("Location: $redirect");
exit;
