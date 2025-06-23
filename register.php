<?php
include 'includes/koneksi.php';

$nama     = $_POST['nama'];
$email    = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role     = $_POST['role'];

// Cek email unik
$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
if (mysqli_num_rows($cek) > 0) {
    header("Location: index.html?status=duplikat");
    exit;
}

$query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')";
if (mysqli_query($koneksi, $query)) {
    header("Location: index.html?status=sukses");
} else {
    header("Location: index.html?status=gagal");
}
exit;
?>
