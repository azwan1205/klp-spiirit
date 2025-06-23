<?php
session_start();
include 'includes/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.html");
  exit;
}

$page = $_GET['page'] ?? 'stok';
$notif = $_SESSION['notif'] ?? '';
unset($_SESSION['notif']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['update_stok'])) {
    $id = $_POST['id'];
    $stok = $_POST['stok'];
    mysqli_query($koneksi, "UPDATE produk SET stok = $stok WHERE id = $id");
    $_SESSION['notif'] = "✅ Stok berhasil diperbarui.";
    header("Location: admin_dashboard.php?page=stok");
    exit;
  }

  if (isset($_POST['hapus_produk'])) {
    $id = $_POST['id'];
    mysqli_query($koneksi, "DELETE FROM produk WHERE id = $id");
    mysqli_query($koneksi, "DELETE FROM riwayat WHERE id_produk = $id");
    mysqli_query($koneksi, "DELETE FROM favorit WHERE id_produk = $id");
    $_SESSION['notif'] = "❌ Produk berhasil dihapus.";
    header("Location: admin_dashboard.php?page=stok");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - SmartSell</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      background: linear-gradient(135deg, #f0f2f5, #dfe6e9);
    }

    .sidebar {
      width: 250px;
      background: linear-gradient(180deg, #0f2027, #203a43, #2c5364);
      color: white;
      height: 100vh;
      padding: 30px 20px;
      position: fixed;
      box-shadow: 4px 0 12px rgba(0,0,0,0.1);
      border-top-right-radius: 14px;
      border-bottom-right-radius: 14px;
    }

    .sidebar h3 {
      font-size: 22px;
      margin-bottom: 40px;
      text-align: center;
      font-weight: 600;
    }

    .sidebar a {
      display: block;
      color: white;
      text-decoration: none;
      padding: 14px 18px;
      margin-bottom: 14px;
      border-radius: 10px;
      transition: all 0.3s ease;
      font-weight: 500;
      letter-spacing: 0.4px;
    }

    .sidebar a:hover, .sidebar a.active {
      background-color: #1abc9c;
      padding-left: 24px;
    }

    .content {
      margin-left: 250px;
      padding: 40px 60px;
      width: calc(100% - 250px);
    }

    .content h2 {
      font-size: 30px;
      margin-bottom: 24px;
      color: #2c3e50;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 16px 14px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #f4f6f9;
      color: #34495e;
    }

    tr:hover {
      background-color: #f0f8ff;
    }

    .success {
      background-color: #d1f7d6;
      color: #256029;
      padding: 15px 20px;
      border-radius: 6px;
      margin-bottom: 20px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
    }

    .btn-hapus {
      background-color: #e74c3c;
      color: white;
      border: none;
      padding: 7px 14px;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn-hapus:hover {
      background-color: #c0392b;
    }

    .btn-update {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 7px 14px;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn-update:hover {
      background-color: #2980b9;
    }

    input[type="number"] {
      padding: 7px;
      border: 1px solid #ccc;
      border-radius: 6px;
      width: 80px;
    }

    form {
      display: flex;
      gap: 8px;
      align-items: center;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h3>Selamat Datang <?= htmlspecialchars($_SESSION['nama']) ?>!</h3>
    <a href="admin_dashboard.php?page=laporan" class="<?= $page === 'laporan' ? 'active' : '' ?>">Laporan Transaksi</a>
    <a href="admin_dashboard.php?page=stok" class="<?= $page === 'stok' ? 'active' : '' ?>">Kelola Stok Produk</a>
    <a href="logout.php">Logout</a>
  </div>

  <div class="content">
    <?php if ($notif): ?>
      <div class="success"><?= $notif ?></div>
    <?php endif; ?>

<?php if ($page === 'laporan'): ?>
  <h2>Laporan Transaksi</h2>
  <table>
    <tr>
      <th>No</th>
      <th>Nama User</th>
      <th>Nama Produk</th>
      <th>Tanggal</th>
      <th>Jumlah</th>
      <th>Total</th>
    </tr>
    <?php
    $result = mysqli_query($koneksi, "
      SELECT u.nama AS nama_user, p.nama_produk, p.harga, t.jumlah, t.tanggal
      FROM transaksi t
      JOIN users u ON t.id_user = u.id
      JOIN produk p ON t.id_produk = p.id
      ORDER BY t.tanggal DESC
    ");
    
    if (!$result) {
      echo "<tr><td colspan='6'>Query error: " . mysqli_error($koneksi) . "</td></tr>";
    } else {
      $no = 1;
      while ($row = mysqli_fetch_assoc($result)) {
        $total = $row['harga'] * $row['jumlah'];
        echo "<tr>
          <td>{$no}</td>
          <td>{$row['nama_user']}</td>
          <td>{$row['nama_produk']}</td>
          <td>" . date('d M Y H:i', strtotime($row['tanggal'])) . "</td>
          <td>{$row['jumlah']}</td>
          <td>Rp" . number_format($total, 0, ',', '.') . "</td>
        </tr>";
        $no++;
      }

        }
        ?>
      </table>

    <?php elseif ($page === 'stok'): ?>
      <h2>Kelola Stok Produk</h2>
      <table>
        <tr>
          <th>No</th>
          <th>Nama Produk</th>
          <th>Stok</th>
          <th>Update</th>
          <th>Hapus</th>
        </tr>
        <?php
        $produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id ASC");
        $no = 1;
        while ($p = mysqli_fetch_assoc($produk)):
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($p['nama_produk']) ?></td>
          <td><?= $p['stok'] ?></td>
          <td>
            <form method="POST">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <input type="number" name="stok" value="<?= $p['stok'] ?>">
              <button type="submit" name="update_stok" class="btn-update">Update</button>
            </form>
          </td>
          <td>
            <form method="POST" onsubmit="return confirm('Yakin hapus produk ini?')">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" name="hapus_produk" class="btn-hapus">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
