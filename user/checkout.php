<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: index.php"); exit; }
require_once '../koneksi.php';
$uid = $_SESSION['id'];

$items = $conn->query("
    SELECT k.jumlah, p.id as pid, p.nama_produk, p.harga, p.stok
    FROM keranjang k JOIN produk p ON k.produk_id=p.id
    WHERE k.pengguna_id=$uid
")->fetch_all(MYSQLI_ASSOC);

if (empty($items)) { header("Location: keranjang.php"); exit; }

// Cek alamat tersimpan
$alamat_data = $conn->query("SELECT * FROM alamat WHERE pengguna_id=$uid")->fetch_assoc();

$total       = array_sum(array_map(fn($i) => $i['harga'] * $i['jumlah'], $items));
$ongkir      = 5000;
$total_bayar = $total + $ongkir;
$error       = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bayar = $_POST['pembayaran'] ?? '';

    if (!$alamat_data) {
        $error = "Kamu belum punya alamat pengiriman! <a href='alamat.php'>Tambah alamat dulu</a>.";
    } elseif (!$bayar) {
        $error = "Pilih metode pembayaran!";
    } else {
        $aid = $alamat_data['id'];
        $conn->query("INSERT INTO pesanan (pengguna_id, alamat_id, pembayaran, total, status) VALUES ($uid, $aid, '$bayar', $total_bayar, 'menunggu')");
        $pid = $conn->insert_id;

        foreach ($items as $item) {
            $conn->query("INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga) VALUES ($pid, {$item['pid']}, {$item['jumlah']}, {$item['harga']})");
            $sb = $item['stok'] - $item['jumlah'];
            $conn->query("UPDATE produk SET stok=$sb WHERE id={$item['pid']}");
        }
        $conn->query("DELETE FROM keranjang WHERE pengguna_id=$uid");
        header("Location: sukses.php?id=$pid"); exit;
    }
}

$jml_keranjang = count($items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Checkout - Toko Alat Tulis</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <span class="brand">Toko Perlengkapan Alat Tulis</span>
    <div class="navbar-links">
        <a href="beranda.php">Beranda</a>
        <a href="pesanan_saya.php">Pesanan Saya</a>
        <a href="alamat.php">Alamat</a>
        <a href="keranjang.php" class="btn-keranjang">Keranjang</a>
        <a href="logout.php" style="color:#888;">Keluar</a>
    </div>
</nav>

<div class="wrap">
    <h2 style="margin-bottom:20px;">Checkout</h2>

    <?php if ($error): ?><div class="alert alert-merah"><?= $error ?></div><?php endif; ?>

    <form method="POST">
    <div class="grid-2">
        <div>
            <!-- Alamat Pengiriman -->
            <div class="kotak" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h3>Alamat Pengiriman</h3>
                    <a href="alamat.php" class="btn btn-abu" style="font-size:0.8rem;padding:5px 12px;">Edit Alamat</a>
                </div>
                <?php if ($alamat_data): ?>
                <div class="kartu-alamat aktif">
                    <div style="font-weight:bold;margin-bottom:4px;"><?= htmlspecialchars($alamat_data['nama_penerima']) ?></div>
                    <div style="color:#888;font-size:0.88rem;margin-bottom:4px;"><?= htmlspecialchars($alamat_data['no_hp']) ?></div>
                    <div style="font-size:0.9rem;"><?= htmlspecialchars($alamat_data['alamat_lengkap']) ?>, <?= htmlspecialchars($alamat_data['kota']) ?>, <?= htmlspecialchars($alamat_data['kode_pos']) ?></div>
                </div>
                <?php else: ?>
                <div class="alert alert-merah">
                    Belum ada alamat tersimpan. <a href="alamat.php"><strong>Tambah alamat sekarang</strong></a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pembayaran -->
            <div class="kotak">
                <h3 style="margin-bottom:14px;">Opsi Pembayaran</h3>
                <label class="opsi-bayar">
                    <input type="radio" name="pembayaran" value="tunai" <?= (($_POST['pembayaran'] ?? '') == 'tunai') ? 'checked' : '' ?> required>
                    <span>&#128181;</span>
                    <div>
                        <div style="font-weight:bold;">Tunai</div>
                        <div style="font-size:0.8rem;color:#888;">Bayar saat pengiriman (COD)</div>
                    </div>
                </label>
                <label class="opsi-bayar">
                    <input type="radio" name="pembayaran" value="transfer" <?= (($_POST['pembayaran'] ?? '') == 'transfer') ? 'checked' : '' ?>>
                    <span>&#128241;</span>
                    <div>
                        <div style="font-weight:bold;">Virtual Akun dan e-wallet</div>
                        <div style="font-size:0.8rem;color:#888;">GoPay / OVO / Dana / Transfer Bank</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="kotak">
            <h3 style="margin-bottom:14px;">Ringkasan Pesanan</h3>
            <?php foreach ($items as $item): ?>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f4f8;font-size:0.9rem;">
                <div>
                    <div style="font-weight:bold;"><?= htmlspecialchars($item['nama_produk']) ?></div>
                    <div style="color:#888;">x<?= $item['jumlah'] ?> &times; Rp <?= number_format($item['harga'],0,',','.') ?></div>
                </div>
                <div style="font-weight:bold;color:#2980b9;">Rp <?= number_format($item['harga']*$item['jumlah'],0,',','.') ?></div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:12px;">
                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:0.88rem;">
                    <span style="color:#888;">Subtotal</span>
                    <span>Rp <?= number_format($total,0,',','.') ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:0.88rem;">
                    <span style="color:#888;">Ongkir</span>
                    <span>Rp <?= number_format($ongkir,0,',','.') ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;font-weight:bold;font-size:1rem;border-top:2px solid #ddd;margin-top:6px;">
                    <span>Total</span>
                    <span style="color:#2980b9;">Rp <?= number_format($total_bayar,0,',','.') ?></span>
                </div>
            </div>
            <?php if ($alamat_data): ?>
            <button type="submit" class="btn btn-biru btn-blok">Pesan Sekarang</button>
            <?php else: ?>
            <a href="alamat.php" class="btn btn-kuning btn-blok">Tambah Alamat Dulu</a>
            <?php endif; ?>
        </div>
    </div>
    </form>
</div>

<div class="footer">&copy; <?= date('Y') ?> Toko Perlengkapan Alat Tulis</div>
</body>
</html>
