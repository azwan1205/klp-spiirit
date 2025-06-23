<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pengguna') {
  header("Location: index.html");
  exit;
}

$notifikasi = $_SESSION['notifikasi'] ?? '';
unset($_SESSION['notifikasi']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Pemilik - SmartSell</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      display: flex;
      height: 100vh;
      background: linear-gradient(to right, #f5f7fa, #c3cfe2);
    }

    .sidebar {
      width: 260px;
      background: linear-gradient(to bottom, #2c3e50, #34495e);
      color: white;
      padding: 30px 20px;
      box-shadow: 4px 0 10px rgba(0,0,0,0.1);
    }

    .sidebar h3 {
      font-size: 18px;
      margin-bottom: 30px;
      text-align: center;
    }

    .sidebar a {
      display: block;
      padding: 12px;
      margin: 14px 0;
      text-decoration: none;
      color: white;
      border-radius: 6px;
      transition: background 0.3s ease;
      font-weight: 500;
    }

    .sidebar a:hover {
      background: #1abc9c;
    }

    .content {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }

    .card {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.06);
      max-width: 600px;
      margin: auto;
    }

    .card h2 {
      margin-bottom: 25px;
      text-align: center;
      color: #2c3e50;
    }

    .profil-container {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .profil-foto {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #1abc9c;
      margin: 20px 0;
    }

    .profil-info {
      width: 100%;
      background: #f7f9fc;
      border-radius: 10px;
      padding: 20px;
      margin-top: 10px;
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
    }

    .profil-info label {
      display: block;
      font-weight: 600;
      margin-top: 15px;
      color: #34495e;
    }

    .profil-info div {
      padding: 10px;
      border-bottom: 1px solid #ecf0f1;
    }

    input[type="file"],
    input[type="text"],
    input[type="number"],
    textarea {
      width: 100%;
      margin-top: 10px;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.2s;
    }

    input:focus,
    textarea:focus {
      border-color: #2980b9;
      outline: none;
    }

    button[type="submit"] {
      width: 100%;
      padding: 12px;
      margin-top: 20px;
      background-color: #27ae60;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 15px;
      transition: background 0.3s ease;
    }

    button[type="submit"]:hover {
      background-color: #219150;
    }

    .alert {
      text-align: center;
      color: green;
      margin-bottom: 15px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h3>Selamat Datang,<br> <?= htmlspecialchars($_SESSION['nama']) ?>!</h3>
    <a href="#" onclick="tampilkanProfil()">Profil</a>
    <a href="#" onclick="tampilkanFormProduk()">Tambah Produk</a>
    <a href="logout.php">Logout</a>
  </div>

  <div class="content">
    <div class="card" id="formProduk">
      <h2>Tambah Produk</h2>
      <?php if ($notifikasi) echo "<div class='alert'>$notifikasi</div>"; ?>
      <form action="tambah_produk.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="nama_produk" placeholder="Nama Produk" required>
        <input type="text" name="merek" placeholder="Merek" required>
        <input type="text" name="kategori" placeholder="Kategori" required>
        <input type="number" name="harga" placeholder="Harga" required>
        <input type="number" name="stok" placeholder="Stok" required>
        <textarea name="deskripsi" rows="4" placeholder="Deskripsi" required></textarea>
        <input type="file" name="gambar">
        <button type="submit">Tambah Produk</button>
      </form>
    </div>

    <div class="card" id="profilPengguna" style="display: none;">
      <h2>Profil</h2>
      <div class="profil-container">
        <label for="fotoInput" style="cursor: pointer; font-weight: 600;">Pilih Foto</label>
        <input type="file" id="fotoInput" accept="image/*" onchange="previewFoto(this)" style="display:none;">
        <img src="assets/images/default-profile.png" alt="Foto Profil" class="profil-foto" id="previewGambar">
        <div class="profil-info">
          <label>Nama</label>
          <div><?= htmlspecialchars($_SESSION['nama']) ?></div>

          <label>Email</label>
          <div><?= htmlspecialchars($_SESSION['email']) ?></div>

          <label>Role</label>
          <div><?= htmlspecialchars($_SESSION['role']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function tampilkanProfil() {
      document.getElementById('formProduk').style.display = 'none';
      document.getElementById('profilPengguna').style.display = 'block';
    }

    function tampilkanFormProduk() {
      document.getElementById('profilPengguna').style.display = 'none';
      document.getElementById('formProduk').style.display = 'block';
    }

    function previewFoto(input) {
      const file = input.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('previewGambar').src = e.target.result;
        }
        reader.readAsDataURL(file);
      }
    }
  </script>
</body>
</html>
