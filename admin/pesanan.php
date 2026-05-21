<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['peran'] != 'admin') {
    header("Location: ../user/index.php"); exit;
}
require_once '../koneksi.php';
$sukses = $error = '';

// Update status — tidak bisa ubah jika sudah selesai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pesanan_id'])) {
    $pid       = (int)$_POST['pesanan_id'];
    $st_baru   = $_POST['status'];
    $valid     = ['menunggu','perlu_dikirim','sedang_dikirim','selesai'];
    $st_skrng  = $conn->query("SELECT status FROM pesanan WHERE id=$pid")->fetch_assoc()['status'];

    if ($st_skrng == 'selesai') {
        $error = "Pesanan #$pid sudah selesai dan tidak bisa diubah!";
    } elseif (in_array($st_baru, $valid)) {
        $conn->query("UPDATE pesanan SET status='$st_baru' WHERE id=$pid");
        $sukses = "Status pesanan #$pid berhasil diperbarui!";
    }
}

$filter = $_GET['filter'] ?? '';
$valid_filter = ['','menunggu','perlu_dikirim','sedang_dikirim','selesai'];
if (!in_array($filter, $valid_filter)) $filter = '';

$where = $filter ? "WHERE p.status='$filter'" : '';
$pesanan = $conn->query("
    SELECT p.*, u.nama AS nama_pembeli,
           a.nama_penerima, a.no_hp, a.alamat_lengkap, a.kota, a.kode_pos
    FROM pesanan p
    JOIN pengguna u ON p.pengguna_id = u.id
    JOIN alamat a ON p.alamat_id = a.id
    $where
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

$atur_id = (int)($_GET['atur'] ?? 0);

$label = [
    'menunggu'       => ['Menunggu',       'b-menunggu'],
    'perlu_dikirim'  => ['Perlu Dikirim',  'b-perlu'],
    'sedang_dikirim' => ['Sedang Dikirim', 'b-kirim'],
    'selesai'        => ['Selesai',        'b-selesai'],
];

// Urutan status (hanya bisa maju, tidak bisa mundur)
$urutan = ['menunggu' => 1, 'perlu_dikirim' => 2, 'sedang_dikirim' => 3, 'selesai' => 4];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Pesanan - Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout-admin">
    <aside class="sidebar">
        <div class="brand">Toko Perlengkapan<br>Alat Tulis</div>
        <nav>
            <a href="index.php">+ Tambah Produk</a>
            <a href="kelola.php">Kelola Produk</a>
            <a href="pesanan.php" class="aktif">Daftar Pesanan</a>
            <a href="../user/logout.php" style="color:rgba(255,255,255,0.6);margin-top:20px;">Keluar</a>
        </nav>
    </aside>
    <main class="konten">
        <h1>Daftar Pesanan Pelanggan</h1>

        <?php if ($sukses): ?><div class="alert alert-hijau"><?= $sukses ?></div><?php endif; ?>
        <?php if ($error):  ?><div class="alert alert-merah"><?= $error ?></div><?php endif; ?>

        <!-- Filter -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
            <a href="pesanan.php" class="btn <?= $filter=='' ? 'btn-biru' : 'btn-abu' ?>" style="font-size:0.82rem;padding:6px 14px;">Semua</a>
            <?php foreach ($label as $key => [$lbl, $bc]): ?>
            <a href="pesanan.php?filter=<?= $key ?>" class="btn <?= $filter==$key ? 'btn-biru' : 'btn-abu' ?>" style="font-size:0.82rem;padding:6px 14px;"><?= $lbl ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($pesanan)): ?>
        <div class="kotak" style="text-align:center;padding:32px;color:#888;">Belum ada pesanan.</div>
        <?php endif; ?>

        <?php foreach ($pesanan as $p):
            [$lb, $bc] = $label[$p['status']] ?? ['—',''];
            $sudah_selesai = $p['status'] == 'selesai';
            $step_skrng = $urutan[$p['status']] ?? 1;
        ?>
        <div class="kartu-pesanan">
            <div class="header">
                <div>
                    <strong>Pesanan #<?= $p['id'] ?></strong>
                    <span style="color:#888;font-size:0.83rem;margin-left:10px;"><?= htmlspecialchars($p['nama_pembeli']) ?></span>
                    <span style="color:#888;font-size:0.82rem;margin-left:10px;"><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="badge <?= $bc ?>"><?= $lb ?></span>
                    <?php if ($sudah_selesai): ?>
                    <span style="font-size:0.78rem;color:#27ae60;font-weight:bold;">&#128274; Terkunci</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="body">
                <!-- Detail produk -->
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:12px;">
                    <div>
                        <div style="font-size:0.78rem;color:#888;font-weight:bold;text-transform:uppercase;margin-bottom:8px;">Produk</div>
                        <?php foreach ($p['detail'] as $d): ?>
                        <div style="font-size:0.88rem;margin-bottom:2px;">
                            <?= htmlspecialchars($d['nama_produk']) ?>
                            <span style="color:#2980b9;font-weight:bold;">x<?= $d['jumlah'] ?></span>
                            <span style="color:#888;">Rp <?= number_format($d['harga'],0,',','.') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div>
                        <div style="font-size:0.78rem;color:#888;font-weight:bold;text-transform:uppercase;margin-bottom:10px;">Total & Pembayaran</div>
                        <div style="font-weight:bold;color:#2980b9;font-size:0.96rem;margin-bottom:2px;">Rp <?= number_format($p['total'],0,',','.') ?></div>
                        <div style="font-size:0.82rem;color:#888;"><?= $p['pembayaran']=='tunai'?'Tunai (COD)':'VA / e-wallet' ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.78rem;color:#888;font-weight:bold;text-transform:uppercase;margin-bottom:8px;">Alamat Pengiriman</div>
                        <div style="font-size:0.88rem;font-weight:bold;margin-bottom:5px;"><?= htmlspecialchars($p['nama_penerima']) ?></div>
                        <div style="font-size:0.82rem;color:#888;margin-bottom:5px;"><?= htmlspecialchars($p['no_hp']) ?></div>
                        <div style="font-size:0.85rem;"><?= htmlspecialchars($p['alamat_lengkap']) ?>, <?= htmlspecialchars($p['kota']) ?>, <?= htmlspecialchars($p['kode_pos']) ?></div>
                    </div>
                </div>

                <!-- Tombol atur — tidak tampil kalau sudah selesai -->
                <?php if (!$sudah_selesai): ?>
                <a href="pesanan.php?atur=<?= $p['id'] ?><?= $filter?'&filter='.$filter:'' ?>"
                   class="btn btn-abu" style="font-size:0.82rem;padding:6px 14px;">
                    <?= $atur_id == $p['id'] ? 'Tutup' : 'Atur Pengiriman' ?>
                </a>
                <?php else: ?>
                <span style="font-size:0.85rem;color:#27ae60;font-weight:bold;">&#10003; Pesanan telah selesai — tidak dapat diubah</span>
                <?php endif; ?>

                <!-- Form atur status — hanya tampil jika belum selesai -->
                <?php if ($atur_id == $p['id'] && !$sudah_selesai): ?>
                <div class="form-atur">
                    <strong style="color:#2980b9;display:block;margin-bottom:10px;">Atur Status Pesanan #<?= $p['id'] ?></strong>
                    <form method="POST" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
                        <select name="status" class="input" style="width:auto;">
                            <?php foreach ($label as $key => [$lbl, $bc]):
                                // Hanya tampilkan status yang lebih tinggi atau sama (tidak bisa mundur)
                                if ($urutan[$key] >= $step_skrng):
                            ?>
                            <option value="<?= $key ?>" <?= $p['status']==$key?'selected':'' ?>><?= $lbl ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-biru" style="font-size:0.88rem;padding:7px 18px;">Simpan</button>
                        <a href="pesanan.php<?= $filter?'?filter='.$filter:'' ?>" class="btn btn-abu" style="font-size:0.88rem;padding:7px 14px;">Batal</a>
                    </form>
                    <p style="font-size:0.8rem;color:#888;margin-top:8px;">* Status hanya bisa maju, tidak bisa mundur. Status <strong>Selesai</strong> akan mengunci pesanan.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </main>
</div>
</body>
</html>
