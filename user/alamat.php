<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: index.php"); exit; }
require_once '../koneksi.php';
$uid = $_SESSION['id'];
$sukses = $error = '';

// Simpan / update alamat
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_p  = trim($_POST['nama_penerima']);
    $hp      = trim($_POST['no_hp']);
    $alamat  = trim($_POST['alamat_lengkap']);
    $kota    = trim($_POST['kota']);
    $kodepos = trim($_POST['kode_pos']);

    if (!$nama_p || !$hp || !$alamat || !$kota || !$kodepos) {
        $error = "Semua kolom wajib diisi!";
    } else {
        $ada = $conn->query("SELECT id FROM alamat WHERE pengguna_id=$uid")->fetch_assoc();
        if ($ada) {
            $conn->query("UPDATE alamat SET nama_penerima='$nama_p', no_hp='$hp', alamat_lengkap='$alamat', kota='$kota', kode_pos='$kodepos' WHERE pengguna_id=$uid");
            $sukses = "Alamat berhasil diperbarui!";
        } else {
            $conn->query("INSERT INTO alamat (pengguna_id, nama_penerima, no_hp, alamat_lengkap, kota, kode_pos) VALUES ($uid, '$nama_p', '$hp', '$alamat', '$kota', '$kodepos')");
            $sukses = "Alamat berhasil disimpan!";
        }
    }
}

$alamat_data   = $conn->query("SELECT * FROM alamat WHERE pengguna_id=$uid")->fetch_assoc();
$jml_keranjang = $conn->query("SELECT SUM(jumlah) as t FROM keranjang WHERE pengguna_id=$uid")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Alamat Pengiriman - Toko Alat Tulis</title>
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

<div class="wrap" style="max-width:600px;">
    <h2 style="margin-bottom:6px;">Alamat Pengiriman</h2>
    <p style="color:#888;margin-bottom:20px;">Alamat ini digunakan untuk semua pesanan kamu.</p>

    <?php if ($sukses): ?><div class="alert alert-hijau"><?= $sukses ?></div><?php endif; ?>
    <?php if ($error):  ?><div class="alert alert-merah"><?= $error ?></div><?php endif; ?>

    <div class="kotak">
        <h3 style="margin-bottom:16px;">
            <?= $alamat_data ? 'Edit Alamat' : 'Tambah Alamat' ?>
        </h3>
        <form method="POST">
            <div class="form-grup">
                <label>Nama Penerima</label>
                <input type="text" name="nama_penerima" class="input" placeholder="Nama lengkap penerima" required value="<?= htmlspecialchars($_POST['nama_penerima'] ?? $alamat_data['nama_penerima'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>No. HP</label>
                <input type="text" name="no_hp" class="input" placeholder="Contoh: 0812-3456-7890" required value="<?= htmlspecialchars($_POST['no_hp'] ?? $alamat_data['no_hp'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>Alamat Lengkap</label>
                <textarea name="alamat_lengkap" class="input" rows="3" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan..." required><?= htmlspecialchars($_POST['alamat_lengkap'] ?? $alamat_data['alamat_lengkap'] ?? '') ?></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-grup">
    <label>Kota</label>
    <input type="text" name="kota" list="list_kota" class="input" placeholder="Kota / Kabupaten" required>
    
    <datalist id="list_kota">
        <option value="Batam">
        <option value="Jakarta">
        <option value="Bandung">
        <option value="Surabaya">
        <option value="Medan">
        <option value="Tanjung Pinang">
    </datalist>
</div>
                <div class="form-grup">
                    <label>Kode Pos</label>
                    <input type="text" name="kode_pos" class="input" placeholder="Contoh: 29444" required value="<?= htmlspecialchars($_POST['kode_pos'] ?? $alamat_data['kode_pos'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-biru">Simpan Alamat</button>
        </form>
    </div>

    <?php if ($alamat_data): ?>
    <div style="margin-top:20px;">
        <h3 style="margin-bottom:10px;font-size:0.95rem;color:#888;">Alamat Tersimpan</h3>
        <div class="kartu-alamat aktif">
            <div style="font-weight:bold;margin-bottom:4px;"><?= htmlspecialchars($alamat_data['nama_penerima']) ?></div>
            <div style="color:#888;font-size:0.88rem;margin-bottom:2px;"><?= htmlspecialchars($alamat_data['no_hp']) ?></div>
            <div style="font-size:0.9rem;"><?= htmlspecialchars($alamat_data['alamat_lengkap']) ?>, <?= htmlspecialchars($alamat_data['kota']) ?>, <?= htmlspecialchars($alamat_data['kode_pos']) ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="footer">&copy; <?= date('Y') ?> Toko Perlengkapan Alat Tulis</div>
</body>
</html>
