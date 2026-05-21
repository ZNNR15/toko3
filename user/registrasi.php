<?php
session_start();
if (isset($_SESSION['id'])) { header("Location: beranda.php"); exit; }
require_once '../koneksi.php';
$error = $sukses = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama    = trim($_POST['nama']);
    $sandi   = trim($_POST['sandi']);
    $konfirm = trim($_POST['konfirm']);

    if (!$nama || !$sandi || !$konfirm) {
        $error = "Semua kolom wajib diisi!";
    } elseif ($sandi !== $konfirm) {
        $error = "Konfirmasi sandi tidak cocok!";
    } else {
        $cek = $conn->query("SELECT id FROM pengguna WHERE nama='$nama'")->num_rows;
        if ($cek > 0) {
            $error = "Nama pengguna sudah digunakan!";
        } else {
            $conn->query("INSERT INTO pengguna (nama, sandi, peran) VALUES ('$nama', '$sandi', 'pelanggan')");
            $sukses = "Akun berhasil dibuat! <a href='index.php'>Login sekarang</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Registrasi - Toko Alat Tulis</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body { background: #d6eaf8; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
.wrap-reg { width: 100%; max-width: 480px; }
.judul { text-align: center; color: #fff; font-size: 1.4rem; font-weight: bold; margin-bottom: 18px; }
</style>
</head>
<body>
<div class="wrap-reg">
    <p class="judul">Toko Perlengkapan Alat Tulis</p>
    <div class="kotak">
        <h3 style="text-align:center;margin-bottom:20px;">Registrasi</h3>
        <?php if ($error): ?><div class="alert alert-merah"><?= $error ?></div><?php endif; ?>
        <?php if ($sukses): ?><div class="alert alert-hijau"><?= $sukses ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-grup">
                <label>Nama</label>
                <input type="text" name="nama" class="input" placeholder="Masukkan nama pengguna" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>Sandi</label>
                <input type="password" name="sandi" class="input" placeholder="Masukkan sandi pengguna" required>
            </div>
            <div class="form-grup">
                <label>Konfirmasi Sandi</label>
                <input type="password" name="konfirm" class="input" placeholder="Konfirmasi sandi" required>
            </div>
            <button type="submit" class="btn btn-biru btn-blok">Daftar</button>
        </form>
        <p style="text-align:center;margin-top:14px;font-size:0.88rem;color:#666;">
            Sudah punya akun? <a href="index.php">Login</a>
        </p>
    </div>
</div>
</body>
</html>
