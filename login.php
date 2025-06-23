<?php
session_start();
include 'includes/koneksi.php';

$email    = $_POST['email'];
$password = $_POST['password'];

// Cari email di database
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
$data = mysqli_fetch_assoc($query);

if ($data && password_verify($password, $data['password'])) {
    $_SESSION['id']   = $data['id'];
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['email'] = $data['email']; // ⬅️ ini penting agar tidak error
    $_SESSION['role'] = $data['role'];

    // Redirect berdasarkan role
    if ($data['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else if ($data['role'] == 'user') {
        header("Location: user_dashboard.php");
    } else if ($data['role'] == 'pengguna') {
        header("Location: pengguna_dashboard.php");
    } else {
        header("Location: index.html");
    }
}
