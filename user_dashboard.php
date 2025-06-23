<?php
session_start();
date_default_timezone_set('Asia/Makassar');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  header("Location: index.html");
  exit;
}
include 'includes/koneksi.php';

$page = $_GET['page'] ?? 'produk';
$id_user = $_SESSION['id'];
$produkList = [];
$riwayatList = [];
$favoritList = [];
$profil = [];

 
if ($page === 'profil') {
  $query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $id_user");
  $profil = mysqli_fetch_assoc($query);
} elseif ($page === 'riwayat') {
  $result = mysqli_query($koneksi, "SELECT r.*, p.nama_produk, p.harga, p.gambar FROM riwayat r JOIN produk p ON r.id_produk = p.id WHERE r.id_user = $id_user ORDER BY r.tanggal DESC");
  while ($row = mysqli_fetch_assoc($result)) $riwayatList[] = $row;
} elseif ($page === 'favorit') {
  $result = mysqli_query($koneksi, "SELECT f.*, p.nama_produk, p.harga, p.gambar FROM favorit f JOIN produk p ON f.id_produk = p.id WHERE f.id_user = $id_user");
  while ($row = mysqli_fetch_assoc($result)) $favoritList[] = $row;
} else {
  $search = mysqli_real_escape_string($koneksi, $_GET['search'] ?? '');
  $query = $search !== ''
    ? "SELECT * FROM produk WHERE nama_produk LIKE '%$search%' ORDER BY id DESC"
    : "SELECT * FROM produk ORDER BY id DESC";
  $result = mysqli_query($koneksi, $query);
  while ($row = mysqli_fetch_assoc($result)) $produkList[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard User - SmartSell</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { margin: 0; background: linear-gradient(135deg, #f0f2f5, #dfe6e9); }
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
    .sidebar a:hover {
      background-color: #1abc9c;
      padding-left: 24px;
    }
    .sidebar a.logout {
      background-color: #1abc9c;
      color: white;
      text-align: center;
      margin-top: 20px;
      font-weight: bold;
    }
    .sidebar a.logout:hover {
      background-color: #16a085;
      transform: scale(1.05);
    }
    .content {
      margin-left: 250px;
      padding: 40px 60px;
      width: calc(100% - 250px);
    }
    h2 {
      font-size: 30px;
      margin-bottom: 24px;
      color: #2c3e50;
    }
    .produk-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 24px;
    }
    .card {
      background: linear-gradient(145deg,rgb(255, 255, 255), #f0f0f0);
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
      padding: 16px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-align: center;
    }
    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
    }
    .card img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 12px;
    }
    .card h3 { font-size: 16px; margin: 10px 0; color: #2c3e50; }
    .card p { font-size: 13px; color: #555; margin: 4px 0; }
    .btn-favorit, .btn-detail, .btn-hapus {
      display: inline-block;
      padding: 6px 12px;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      cursor: pointer;
      margin-top: 6px;
      font-weight: 500;
    }
    .btn-favorit {
      background: linear-gradient(90deg, #e74c3c, #ff6f61);
      color: #fff;
      margin-right: 6px;
    }
    .btn-favorit:hover { background:hsl(6, 63.40%, 46.10%); }
    .btn-detail {
      background: linear-gradient(90deg, #3498db, #2980b9);
      color: #fff;
    }
    .btn-detail:hover { background: #2471a3; }
    .btn-hapus:hover {
      background:rgb(216, 0, 0);
    }
    .notif {
      background-color: #dff9fb;
      color: #130f40;
      padding: 14px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-weight: 500;
    }
    .profil-card {
      max-width: 400px;
      margin: auto;
      background: white;
      border-radius: 18px;
      padding: 28px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
      text-align: center;
    }
    .profil-card img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #16a085;
      margin-bottom: 15px;
    }
    .profil-card p {
      font-size: 14px;
      color: #333;
      margin: 10px 0;
      line-height: 1.4;
    }
    form[method="GET"] {
      margin-bottom: 24px;
    }
    .card.profil-card {
  max-width: 380px;
  margin: 30px auto;
  background: #ffffff;
  border-radius: 20px;
  padding: 32px 24px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
  text-align: center;
  transition: all 0.3s ease;
}

.card.profil-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.1);
}

.card.profil-card label {
  font-size: 15px;
  font-weight: 600;
  color: #2980b9;
  cursor: pointer;
  display: block;
  margin-bottom: 10px;
  transition: color 0.3s ease;
}

.card.profil-card label:hover {
  color: #1abc9c;
}

.card.profil-card img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #1abc9c;
  margin-bottom: 20px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.card.profil-card p {
  font-size: 14px;
  color: #2c3e50;
  margin: 12px 0;
  line-height: 1.5;
}

.card.profil-card p strong {
  display: block;
  font-size: 14px;
  color: #34495e;
  margin-bottom: 3px;
}

  </style>
</head>
<body>

<div class="sidebar">
  <h3>Selamat Datang<br><?= htmlspecialchars($_SESSION['nama']) ?>!</h3>
  <a href="user_dashboard.php">Daftar Produk</a>
  <a href="user_dashboard.php?page=riwayat">Riwayat Pembelian</a>
  <a href="user_dashboard.php?page=favorit">Favorit Produk</a>
  <a href="user_dashboard.php?page=profil">Profil</a>
  <a href="logout.php">Logout</a>
</div>

<div class="content">
  <?php if (isset($_SESSION['notif'])): ?>
    <div class="notif"><?= $_SESSION['notif'] ?></div>
    <?php unset($_SESSION['notif']); ?>
  <?php endif; ?>

  <?php if ($page === 'riwayat'): ?>
    <h2>Riwayat Pembelian</h2>
    <div class="produk-grid">
      <?php foreach ($riwayatList as $r): ?>
        <div class="card">
          <img src="uploads/<?= htmlspecialchars($r['gambar']) ?>" alt="<?= htmlspecialchars($r['nama_produk']) ?>">
          <h3><?= htmlspecialchars($r['nama_produk']) ?></h3>
          <p><strong>Harga Satuan:</strong> Rp<?= number_format($r['harga'], 0, ',', '.') ?></p>
          <p><strong>Jumlah:</strong> <?= $r['jumlah'] ?></p>
          <p><strong>Total:</strong> Rp<?= number_format($r['harga'] * $r['jumlah'], 0, ',', '.') ?></p>
          <p><strong>Tanggal:</strong>
            <?php
              $tgl = date('d', strtotime($r['tanggal']));
              $bln = date('m', strtotime($r['tanggal']));
              $thn = date('Y', strtotime($r['tanggal']));
              $jam = date('H:i', strtotime($r['tanggal']));
              $bulanIndo = [
                '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
              ];
              echo "$tgl {$bulanIndo[$bln]} $thn - $jam";
            ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>

  <?php elseif ($page === 'favorit'): ?>
    <h2>Favorit Produk</h2>
    <div class="produk-grid">
      <?php foreach ($favoritList as $fav): ?>
        <div class="card">
          <img src="uploads/<?= htmlspecialchars($fav['gambar']) ?>" alt="<?= htmlspecialchars($fav['nama_produk']) ?>">
          <h3><?= htmlspecialchars($fav['nama_produk']) ?></h3>
          <p><strong>Harga:</strong> Rp<?= number_format($fav['harga'], 0, ',', '.') ?></p>
          <form method="POST" action="hapus_favorit.php" style="display:inline;">
            <input type="hidden" name="id_produk" value="<?= $fav['id_produk'] ?>">
            <input type="hidden" name="current_page" value="<?= $_SERVER['REQUEST_URI'] ?>">
            <button class="btn-hapus" onclick="return confirm('Hapus dari favorit?')">Hapus</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

  <?php elseif ($page === 'profil'): ?>
    <h2 style="text-align:center;">Profil</h2>
<div class="card profil-card">
  <label for="fotoInput">Pilih Foto</label>
  <input type="file" id="fotoInput" accept="image/*" onchange="previewFoto(this)" style="display:none;">
  <img src="uploads/<?= htmlspecialchars($profil['foto'] ?? 'default.png') ?>" alt="Foto Profil" id="previewGambar" onclick="document.getElementById('fotoInput').click();">
  
  <p><strong>Nama</strong><?= htmlspecialchars($profil['nama']) ?></p>
  <p><strong>Email</strong><?= htmlspecialchars($profil['email']) ?></p>
  <p><strong>Role</strong><?= htmlspecialchars($profil['role']) ?></p>
</div>

    </div>
    <script>
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

<?php else: ?>
<!-- selamat datang di smartsell -->
<style>
  .judul-welcome {
    font-size: 36px;
    background: linear-gradient(90deg, #2980b9, #1abc9c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    margin-bottom: 5px;
  }
  .judul-sub {
    font-size: 22px;
    color: #34495e;
    font-weight: 500;
    margin-bottom: 20px;
  }
  .deskripsi-sub {
  font-size: 16px;
  color: #555;
  margin-top: -8px;
  margin-bottom: 24px;
  font-weight: 400;
  max-width: 800px;
}

</style>

<h2 class="judul-welcome">Selamat Datang di SmartSell</h2>
<p class="deskripsi-sub">Temukan berbagai pilihan produk handphone terbaik dengan harga menarik dan terpercaya,Silakan pilih-pilih dulu ya😄</p>


<!-- filter pencarian -->
<style>
.search-wrapper {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  width: 90%;
  max-width: none;
}

.search-wrapper input[type="text"] {
  flex: 1;
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 15px;
}

  .search-wrapper button {
    padding: 10px 18px;
    background: #2980b9;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  .search-wrapper button:hover {
    background: #2471a3;
  }
</style>

<form method="GET" class="search-wrapper">
  <input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
  <button type="submit">Cari</button>
</form>

<!-- Tombol Filter Merek -->
<style>
  .brand-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin: 20px 0 30px;
  }
  .brand-buttons button {
    padding: 10px 18px;
    background: #ffffff;
    border: 2px solid #3498db;
    border-radius: 25px;
    color: #3498db;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
  }
  .brand-buttons button:hover {
    background: #3498db;
    color: #fff;
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }
</style>

<div class="brand-buttons">
  <button onclick="filterBrand('Semua')">Semua</button>
  <button onclick="filterBrand('Samsung')">Samsung</button>
  <button onclick="filterBrand('Iphone')">Iphone</button>
  <button onclick="filterBrand('Oppo')">Oppo</button>
  <button onclick="filterBrand('Xiaomi')">Xiaomi</button>
  <button onclick="filterBrand('vivo')">vivo</button>
  <button onclick="filterBrand('Realme')">Realme</button>
</div>

<h3 class=>Daftar Produk</h3>

    <div class="produk-grid">
      <?php if (count($produkList) === 0): ?>
        <p>Tidak ada produk ditemukan.</p>
      <?php else: ?>
        <?php foreach ($produkList as $produk): ?>
          <div class="card">
            <img src="uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
            <h3><?= htmlspecialchars($produk['nama_produk']) ?></h3>
            <p><strong>Harga:</strong> Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
            <p><strong>Stok:</strong> <?= $produk['stok'] ?></p>
            <form method="POST" action="tambah_favorit.php" style="display:inline;">
              <input type="hidden" name="id_produk" value="<?= $produk['id'] ?>">
              <input type="hidden" name="current_page" value="<?= $_SERVER['REQUEST_URI'] ?>">
              <button class="btn-favorit">❤ Favorit</button>
            </form>
            <a class="btn-detail" href="user_detail_produk.php?id=<?= $produk['id'] ?>">Lihat Detail</a>
          </div>
        <?php endforeach; ?>
        
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<!-- SCRIPT FILTER MEREK -->
<script>
function filterBrand(brand) {
  const cards = document.querySelectorAll(".produk-grid .card");
  cards.forEach(card => {
    const productName = card.querySelector("h3").innerText.toLowerCase();
    if (brand === 'Semua' || productName.includes(brand.toLowerCase())) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}
</script>
</body>
</html>
