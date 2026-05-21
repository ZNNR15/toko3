<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: index.php"); exit; }
require_once '../koneksi.php';
$uid = $_SESSION['id'];

$pesanan = $conn->query("
    SELECT p.*, a.nama_penerima, a.alamat_lengkap, a.kota, a.no_hp
    FROM pesanan p
    JOIN alamat a ON p.alamat_id = a.id
    WHERE p.pengguna_id = $uid
    ORDER BY p.tanggal DESC
")->fetch_all(MYSQLI_ASSOC);

foreach ($pesanan as &$p) {
    $pid = $p['id'];
    $p['detail'] = $conn->query("
        SELECT dp.jumlah, dp.harga, pr.nama_produk
        FROM detail_pesanan dp JOIN produk pr ON dp.produk_id=pr.id
        WHERE dp.pesanan_id=$pid
    ")->fetch_all(MYSQLI_ASSOC);
}
unset($p);

$jml_keranjang = $conn->query("SELECT SUM(jumlah) as t FROM keranjang WHERE pengguna_id=$uid")->fetch_assoc()['t'] ?? 0;

$label = [
    'menunggu'       => ['Menunggu',       'b-menunggu'],
    'perlu_dikirim'  => ['Perlu Dikirim',  'b-perlu'],
    'sedang_dikirim' => ['Sedang Dikirim', 'b-kirim'],
    'selesai'        => ['Selesai',        'b-selesai'],
];

$urutan = ['menunggu' => 1, 'perlu_dikirim' => 2, 'sedang_dikirim' => 3, 'selesai' => 4];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pesanan Saya - Toko Alat Tulis</title>
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
    <h2 style="margin-bottom:20px;">Pesanan Saya</h2>

    <?php if (empty($pesanan)): ?>
    <div class="kotak" style="text-align:center;padding:40px;">
        <p style="color:#888;margin-bottom:16px;">Belum ada pesanan.</p>
        <a href="beranda.php" class="btn btn-biru">Mulai Belanja</a>
    </div>
    <?php endif; ?>

    <?php foreach ($pesanan as $p):
        [$lb, $bc] = $label[$p['status']] ?? ['—',''];
        $step = $urutan[$p['status']] ?? 1;
    ?>
    <div class="kartu-pesanan">
        <div class="header">
            <div>
                <strong>Pesanan #<?= $p['id'] ?></strong>
                <span style="color:#888;font-size:0.83rem;margin-left:10px;"><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></span>
            </div>
            <span class="badge <?= $bc ?>"><?= $lb ?></span>
        </div>
        <div class="body">
            <!-- Progress bar status -->
            <div class="progress-status">
                <?php
                $steps = [
                    1 => 'Menunggu',
                    2 => 'Diproses',
                    3 => 'Dikirim',
                    4 => 'Selesai',
                ];
                $i = 1;
                foreach ($steps as $num => $nama_step):
                    $kelas = '';
                    if ($num < $step) $kelas = 'selesai';
                    elseif ($num == $step) $kelas = 'aktif';
                ?>
                <?php if ($i > 1): ?>
                <div class="step-line <?= $num <= $step ? 'selesai' : '' ?>"></div>
                <?php endif; ?>
                <div class="step <?= $kelas ?>">
                    <div class="dot"><?= $num < $step ? '&#10003;' : $num ?></div>
                    <span><?= $nama_step ?></span>
                </div>
                <?php $i++; endforeach; ?>
            </div>

            <!-- Detail produk -->
            <div style="margin-bottom:12px;">
                <?php foreach ($p['detail'] as $d): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f0f4f8;font-size:0.9rem;">
                    <span><?= htmlspecialchars($d['nama_produk']) ?> <span style="color:#888;">x<?= $d['jumlah'] ?></span></span>
                    <span style="color:#2980b9;font-weight:bold;">Rp <?= number_format($d['harga']*$d['jumlah'],0,',','.') ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Alamat & total -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:0.88rem;">
                <div>
                    <div style="font-weight:bold;color:#888;margin-bottom:8px;">Dikirim ke:</div>
                    <div style="font-weight:bold;margin-bottom:4px;"><?= htmlspecialchars($p['nama_penerima']) ?></div>
                    <div style="color:#888;margin-bottom:4px;"><?= htmlspecialchars($p['no_hp']) ?></div>
                    <div><?= htmlspecialchars($p['alamat_lengkap']) ?>, <?= htmlspecialchars($p['kota']) ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:bold;color:#888;margin-bottom:14px;">Total Pembayaran:</div>
                    <div style="font-size:1.1rem;font-weight:bold;color:#2980b9;margin-bottom:4px;">Rp <?= number_format($p['total'],0,',','.') ?></div>
                    <div style="color:#888;font-size:0.82rem;"><?= $p['pembayaran'] == 'tunai' ? 'Tunai (COD)' : 'VA / e-wallet' ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="footer">&copy; <?= date('Y') ?> Toko Perlengkapan Alat Tulis</div>
</body>
</html>
