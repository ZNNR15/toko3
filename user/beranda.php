<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: index.php"); exit; }
require_once '../koneksi.php';
$uid = $_SESSION['id'];
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pid'])) {
    $pid = (int)$_POST['pid'];
    $cek = $conn->query("SELECT id, jumlah FROM keranjang WHERE pengguna_id=$uid AND produk_id=$pid")->fetch_assoc();
    if ($cek) {
        $j = $cek['jumlah'] + 1;
        $conn->query("UPDATE keranjang SET jumlah=$j WHERE id={$cek['id']}");
    } else {
        $conn->query("INSERT INTO keranjang (pengguna_id, produk_id, jumlah) VALUES ($uid, $pid, 1)");
    }
    $pesan = "Produk ditambahkan ke keranjang!";
}

$produk        = $conn->query("SELECT * FROM produk ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$jml_keranjang = $conn->query("SELECT SUM(jumlah) as t FROM keranjang WHERE pengguna_id=$uid")->fetch_assoc()['t'] ?? 0;

function ikon($nama) {
    $n = strtolower($nama);
    if (strpos($n,'buku') !== false)      return '&#128211;';
    if (strpos($n,'pena') !== false)      return '&#128394;';
    if (strpos($n,'pensil') !== false)    return '&#9999;';
    if (strpos($n,'penghapus') !== false) return '&#129529;';
    if (strpos($n,'penggaris') !== false) return '&#128207;';
    if (strpos($n,'tipp') !== false)      return '&#128397;';
    return '&#128230;';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Beranda - Toko Alat Tulis</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <span class="brand">Toko Perlengkapan Alat Tulis</span>
    <div class="navbar-links">
        <a href="beranda.php">Beranda</a>
        <a href="pesanan_saya.php">Pesanan Saya</a>
        <a href="alamat.php">Alamat</a>
        <a href="keranjang.php" class="btn-keranjang">
            Keranjang
            <?php if ($jml_keranjang > 0): ?>
            <span style="background:#e74c3c;border-radius:50%;padding:1px 6px;font-size:0.75rem;margin-left:4px;"><?= $jml_keranjang ?></span>
            <?php endif; ?>
        </a>
        <a href="logout.php" style="color:#888;">Keluar</a>
    </div>
</nav>

<div class="hero">
    <p class="halo">
        Halo, <?= htmlspecialchars($_SESSION['nama']) ?>!</p>
    <h1>Perlengkapan Sekolah Lengkap &amp; Murah</h1>
    <p>Temukan semua kebutuhan belajarmu di sini</p>
    <a href="#produk" class="btn btn-abu">Belanja Sekarang</a>
</div>

<div class="wrap" id="produk">
    <?php if ($pesan): ?>
    <div class="alert alert-hijau"><?= $pesan ?></div>
    <?php endif; ?>

    <h2 style="margin-bottom:4px;">Produk Terbaru</h2>
    <p style="color:#888;margin-bottom:0;">Pilih produk favoritmu</p>

    <div class="grid">
    <?php foreach ($produk as $p): ?>
        <div class="kartu-produk">
            <?php if ($p['gambar'] && file_exists("../uploads/".$p['gambar'])): ?>
                <img src="../uploads/<?= $p['gambar'] ?>" alt="">
            <?php else: ?>
                <div class="ikon"><?= ikon($p['nama_produk']) ?></div>
            <?php endif; ?>
            <div class="nama"><?= htmlspecialchars($p['nama_produk']) ?></div>
            <div class="harga">Rp <?= number_format($p['harga'],0,',','.') ?></div>
            <div class="stok">Stok: <?= $p['stok'] ?></div>
            <?php if ($p['stok'] > 0): ?>
            <form method="POST">
                <input type="hidden" name="pid" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-biru btn-blok" style="font-size:0.82rem;padding:6px;">Beli</button>
            </form>
            <?php else: ?>
            <button class="btn btn-abu btn-blok" style="font-size:0.82rem;padding:6px;" disabled>Habis</button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<div class="footer">&copy; <?= date('Y') ?> Toko Perlengkapan Alat Tulis</div>
</body>
</html>
