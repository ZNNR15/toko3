<?php
session_start();
if (isset($_SESSION['id'])) {
    if ($_SESSION['peran'] == 'admin') header("Location: ../admin/index.php");
    else header("Location: beranda.php");
    exit;
}
require_once '../koneksi.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama  = trim($_POST['nama']);
    $sandi = trim($_POST['sandi']);
    $hasil = $conn->query("SELECT * FROM pengguna WHERE nama='$nama' AND sandi='$sandi'")->fetch_assoc();
    if ($hasil) {
        $_SESSION['id']    = $hasil['id'];
        $_SESSION['nama']  = $hasil['nama'];
        $_SESSION['peran'] = $hasil['peran'];
        if ($hasil['peran'] == 'admin') header("Location: ../admin/index.php");
        else header("Location: beranda.php");
        exit;
    } else {
        $error = "Nama atau sandi salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Toko Alat Tulis</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body { background: #d6eaf8; display: flex; min-height: 100vh; }
.panel-kiri { width: 320px; background: #85c1e9; display: flex; align-items: flex-end; padding: 28px; flex-shrink: 0; }
.panel-kiri h2 { color: #fcf9c7; font-size: 1.1rem; line-height: 1.5; padding: 32px; }
.panel-kanan { flex: 1; display: flex; align-items: center; justify-content: center; padding: 28px; }
.kotak-login { background: #fff; border-radius: 10px; padding: 32px 28px; width: 100%; max-width: 450px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
.kotak-login h3 { text-align: center; margin-bottom: 22px; font-size: 1.1rem; }
.link-lupa { display: block; text-align: right; font-size: 0.83rem; margin-top: -8px; margin-bottom: 16px; }
.daftar { text-align: center; margin-top: 16px; font-size: 0.88rem; color: #666; }
@media(max-width:500px){ .panel-kiri{display:none;} }
</style>
</head>
<body>
<div class="panel-kiri">
        <h2>Toko Perlengkapan<br>Alat Tulis</h2>
</div>
<div class="panel-kanan">
    <div class="kotak-login">
        <h3>login untuk melanjutkan</h3>
        <?php if ($error): ?><div class="alert alert-merah"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-grup">
                <label>nama:</label>
                <input type="text" name="nama" class="input" placeholder="Masukkan nama pengguna" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>sandi</label>
                <input type="password" name="sandi" class="input" placeholder="Masukkan sandi pengguna" required>
            </div>
            <a href="lupa_sandi.php" class="link-lupa">Lupa sandi?</a>
            <button type="submit" class="btn btn-biru btn-blok">masuk</button>
        </form>
        <p class="daftar">Belum punya akun? <a href="registrasi.php">registrasi</a></p>
    </div>
</div>
</body>
</html>
