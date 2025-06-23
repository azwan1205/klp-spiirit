<?php
session_start();
date_default_timezone_set('Asia/Makassar');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  header("Location: index.html");
  exit;
}
include 'includes/koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<p>Produk tidak ditemukan.</p>";
  exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $id");
if (mysqli_num_rows($result) == 0) {
  echo "<p>Produk tidak ditemukan.</p>";
  exit;
}

$produk = mysqli_fetch_assoc($result);
$transaksi_sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jumlah'])) {
  $jumlah = intval($_POST['jumlah']);
  $id_user = $_SESSION['id'];
  $tanggal = date('Y-m-d H:i:s');
  $id_produk = $produk['id'];

  if ($jumlah > 0 && $jumlah <= $produk['stok']) {
    $stmt = $koneksi->prepare("INSERT INTO transaksi (id_produk, id_user, jumlah, tanggal) VALUES (?, ?, ?, ?)");
    if ($stmt) {
      $stmt->bind_param("iiis", $id_produk, $id_user, $jumlah, $tanggal);
      $stmt->execute();

      $sisa = $produk['stok'] - $jumlah;
      mysqli_query($koneksi, "UPDATE produk SET stok = $sisa WHERE id = $id_produk");
      mysqli_query($koneksi, "INSERT INTO riwayat (id_user, id_produk, jumlah, tanggal) VALUES ($id_user, $id_produk, $jumlah, '$tanggal')");

      $produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $id"));
      $transaksi_sukses = true;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Produk - <?= htmlspecialchars($produk['nama_produk']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      margin: 0;
      background: linear-gradient(to right, #f4f6fb, #e9efff);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }

    .detail-card {
      background: white;
      padding: 32px 26px;
      border-radius: 18px;
      box-shadow: 0 18px 30px rgba(0,0,0,0.07);
      text-align: center;
      width: 100%;
      max-width: 420px;
      animation: fadeIn 0.4s ease-in-out;
    }

    .detail-card img {
      width: 100%;
      max-height: 220px;
      object-fit: contain;
      border-radius: 14px;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }

    .detail-card img:hover {
      transform: scale(1.03);
    }

    .detail-card h2 {
      font-size: 22px;
      color: #2c3e50;
      margin-bottom: 10px;
    }

    .detail-card p {
      font-size: 14px;
      margin: 4px 0;
      color: #555;
    }
    .detail-card p strong {
      color: #222;
      font-weight: 600;
    }

    .form-beli {
      margin-top: 24px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      align-items: center;
    }

    .form-beli input[type="number"] {
      width: 60px;
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 8px;
      text-align: center;
      font-weight: bold;
    }

    .btn-beli, .btn-kembali {
      padding: 8px 20px;
      border: none;
      border-radius: 30px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      max-width: 220px;
    }

    .btn-beli {
      background: linear-gradient(to right, #2980b9, #3498db);
      color: white;
    }

    .btn-beli:hover {
      background: #2471a3;
    }

    .btn-kembali {
      background: #dcdde1;
      color: #2c3e50;
    }
    .btn-kembali:hover {
      background: #bdc3c7;
    }

    /* Modal Konfirmasi */
    .modal-konfirmasi {
      background: white;
      padding: 30px 24px;
      border-radius: 18px;
      max-width: 380px;
      text-align: center;
      box-shadow: 0 12px 30px rgba(255, 0, 157, 0.15);
      animation: fadeIn 0.3s ease-in-out;
    }

    .modal-konfirmasi h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 12px;
      color: #2c3e50;
    }

    .modal-konfirmasi p {
      font-size: 14px;
      margin-bottom: 20px;
      color: #555;
    }

    .modal-konfirmasi button {
      padding: 10px 20px;
      margin: 0 6px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 500;
      font-size: 13px;
      transition: all 0.3s ease;
    }

    .modal-konfirmasi button:first-child {
      background-color: #2980b9;
      color: white;
    }

    .modal-konfirmasi button:first-child:hover {
      background-color: #2471a3;
    }

    .modal-konfirmasi button:last-child {
      background-color: #ccc;
      color: #333;
    }

    .modal-konfirmasi button:last-child:hover {
      background-color: #aaa;
    }

    /* Modal Pembayaran */
    #modalPembayaran {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.35);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 10000;
      animation: fadeIn 0.3s ease-in-out;
    }

    #modalPembayaran .box {
      background: white;
      padding: 30px 24px;
      border-radius: 20px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.15);
      width: 360px;
      text-align: left;
    }

    #modalPembayaran h2 {
      font-size: 20px;
      color: #2c3e50;
      margin-bottom: 18px;
      text-align: center;
    }

    #modalPembayaran h2::before {
      content: "🧾 ";
    }

    #modalPembayaran table {
      font-size: 14px;
      margin-bottom: 16px;
    }

    #modalPembayaran p {
      font-size: 14px;
      color: #444;
      margin-bottom: 20px;
    }

    #modalPembayaran button {
      padding: 8px 16px;
      border: none;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 500;
      margin-top: 10px;
      cursor: pointer;
      margin-right: 10px;
    }

    #modalPembayaran button:first-child {
      background: #2e86de;
      color: white;
    }

    #modalPembayaran button.secondary {
      background: #bdc3c7;
      color: #2c3e50;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
  </style>
</head>
<body>

<div class="detail-card">
  <img src="uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
  <h2><?= htmlspecialchars($produk['nama_produk']) ?></h2>
  <p><strong>Harga:</strong> Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
  <p><strong>Stok:</strong> <?= $produk['stok'] ?></p>
  <p><strong>Deskripsi:</strong> <?= htmlspecialchars($produk['deskripsi']) ?></p>

  <?php if ($produk['stok'] > 0): ?>
    <form class="form-beli" method="POST" onsubmit="return showConfirmation(event)">
      <input type="number" name="jumlah" min="1" max="<?= $produk['stok'] ?>" value="1">
      <button type="submit" class="btn-beli">Beli</button>
      <button type="button" class="btn-kembali" onclick="window.location='user_dashboard.php'">Kembali</button>
    </form>
  <?php else: ?>
    <p style="color:red; font-weight:bold; margin-top:20px;">⚠️ Stok produk ini telah habis.</p>
    <button class="btn-kembali" onclick="window.location='user_dashboard.php'">Kembali</button>
  <?php endif; ?>
</div>

<?php if ($transaksi_sukses): ?>
  <div id="modalPembayaran">
    <div class="box">
      <h2>Struk Pembayaran</h2>
      <table>
        <tr><td>Produk</td><td>: <?= htmlspecialchars($produk['nama_produk']) ?></td></tr>
        <tr><td>Harga</td><td>: Rp<?= number_format($produk['harga'], 0, ',', '.') ?></td></tr>
        <tr><td>Jumlah</td><td>: <?= $jumlah ?></td></tr>
        <tr><td>Total</td><td>: Rp<?= number_format($produk['harga'] * $jumlah, 0, ',', '.') ?></td></tr>
        <tr><td>Waktu</td><td>: <?= date('d M Y - H:i', strtotime($tanggal)) ?></td></tr>
      </table>
      <p>Terima kasih telah berbelanja di SmartSell 💙</p>
      <button onclick="window.location='user_dashboard.php?page=riwayat'">Lihat Riwayat Transaksi</button>
      <button class="secondary" onclick="window.location='user_dashboard.php'">Kembali</button>
    </div>
  </div>
<?php endif; ?>

<script>
function showConfirmation(event) {
  event.preventDefault();
  const modal = document.createElement('div');
  modal.style.position = 'fixed';
  modal.style.top = 0;
  modal.style.left = 0;
  modal.style.width = '100%';
  modal.style.height = '100%';
  modal.style.background = 'rgba(0,0,0,0.4)';
  modal.style.display = 'flex';
  modal.style.justifyContent = 'center';
  modal.style.alignItems = 'center';
  modal.style.zIndex = 10000;

  modal.innerHTML = `
    <div class="modal-konfirmasi">
      <h3>Konfirmasi Pembelian</h3>
      <p>Apakah Anda yakin ingin membeli produk ini?</p>
      <button onclick="submitBeli()">Ya</button>
      <button onclick="this.closest('div').parentElement.remove()">Batal</button>
    </div>
  `;
  document.body.appendChild(modal);
  return false;
}

function submitBeli() {
  document.querySelector('form.form-beli').submit();
}
</script>
</body>
</html>
