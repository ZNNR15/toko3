<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: index.php"); exit; }
require_once '../koneksi.php';
$uid = $_SESSION['id'];

if (isset($_GET['hapus'])) {
    $kid = (int)$_GET['hapus'];
    $conn->query("DELETE FROM keranjang WHERE id=$kid AND pengguna_id=$uid");
    header("Location: keranjang.php"); exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['jumlah'] as $kid => $jml) {
        $jml = max(1, (int)$jml);
        $conn->query("UPDATE keranjang SET jumlah=$jml WHERE id=$kid AND pengguna_id=$uid");
    }
    header("Location: keranjang.php"); exit;
}

$items = $conn->query("
    SELECT k.id as kid, k.jumlah, p.nama_produk, p.harga
    FROM keranjang k JOIN produk p ON k.produk_id=p.id
    WHERE k.pengguna_id=$uid
")->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($items as $i) $total += $i['harga'] * $i['jumlah'];
$jml_keranjang = $conn->query("SELECT SUM(jumlah) as t FROM keranjang WHERE pengguna_id=$uid")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Keranjang - Toko Alat Tulis</title>
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

<div class="wrap">
    <h2 style="margin-bottom:20px;">Keranjang Belanjaan Anda</h2>

    <?php if (empty($items)): ?>
    <div class="kotak" style="text-align:center;padding:40px;">
        <p style="color:#888;margin-bottom:16px;">Keranjang masih kosong.</p>
        <a href="beranda.php" class="btn btn-biru">Mulai Belanja</a>
    </div>
    <?php else: ?>
    <form method="POST">
    <div class="tabel-wrap">
    <table>
        <thead>
            <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['nama_produk']) ?></td>
            <td>Rp <?= number_format($item['harga'],0,',','.') ?></td>
            <td>
                <input type="number" name="jumlah[<?= $item['kid'] ?>]" value="<?= $item['jumlah'] ?>" min="1" class="input" style="width:65px;padding:5px 8px;">
            </td>
            <td style="font-weight:bold;color:#2980b9;">Rp <?= number_format($item['harga']*$item['jumlah'],0,',','.') ?></td>
            <td><a href="keranjang.php?hapus=<?= $item['kid'] ?>" class="btn btn-merah" style="font-size:0.82rem;padding:5px 12px;">Hapus</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;gap:10px;">
        <button type="submit" class="btn btn-abu">Perbarui Keranjang</button>
        <div style="background:#d6eaf8;padding:12px 20px;border-radius:8px;font-weight:bold;font-size:1rem;">
            Total: <span style="color:#2980b9;">Rp <?= number_format($total,0,',','.') ?></span>
        </div>
    </div>
    </form>
    <div style="margin-top:16px;text-align:right;">
        <a href="beranda.php" class="btn btn-abu" style="margin-right:8px;">Lanjut Belanja</a>
        <a href="checkout.php" class="btn btn-biru">Pesan</a>
    </div>
    <?php endif; ?>
</div>

<div class="footer">&copy; <?= date('Y') ?> Toko Perlengkapan Alat Tulis</div>
</body>
</html>
