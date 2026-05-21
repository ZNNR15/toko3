<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['peran'] != 'admin') {
    header("Location: ../user/index.php"); exit;
}
require_once '../koneksi.php';
$sukses = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama  = trim($_POST['nama_produk']);
    $harga = (int)$_POST['harga'];
    $stok  = (int)$_POST['stok'];
    $gambar = null;

    if (!$nama || $harga <= 0) {
        $error = "Nama produk dan harga wajib diisi!";
    } else {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','gif','webp']) && $_FILES['gambar']['size'] <= 2*1024*1024) {
                $folder = __DIR__ . '/../uploads/';
                if (!is_dir($folder)) mkdir($folder, 0777, true);
                $gambar = uniqid().'.'.$ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], $folder.$gambar);
            }
        }
        $g = $gambar ? "'$gambar'" : "NULL";
        $conn->query("INSERT INTO produk (nama_produk, harga, stok, gambar) VALUES ('$nama', $harga, $stok, $g)");
        $sukses = "Produk berhasil ditambahkan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Produk - Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout-admin">
    <aside class="sidebar">
        <div class="brand">Toko Perlengkapan<br>Alat Tulis</div>
        <nav>
            <a href="index.php" class="aktif">+ Tambah Produk</a>
            <a href="kelola.php">Kelola Produk</a>
            <a href="pesanan.php">Daftar Pesanan</a>
            <a href="../user/logout.php" style="color:rgba(255,255,255,0.6);margin-top:20px;">Keluar</a>
        </nav>
    </aside>
    <main class="konten">
        <h1>Halaman Admin</h1>
        <div class="tab">
            <a href="index.php" class="aktif">Tambah Produk</a>
            <a href="kelola.php">Kelola Produk</a>
        </div>
        <?php if ($sukses): ?><div class="alert alert-hijau"><?= $sukses ?></div><?php endif; ?>
        <?php if ($error):  ?><div class="alert alert-merah"><?= $error ?></div><?php endif; ?>
        <div class="kotak" style="max-width:460px;">
            <h3 style="margin-bottom:16px;">Tambah Produk</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grup">
                    <label>Nama Produk:</label>
                    <input type="text" name="nama_produk" class="input" placeholder="Contoh: Buku Tulis" required value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>">
                </div>
                <div class="form-grup">
                    <label>Harga:</label>
                    <input type="number" name="harga" class="input" placeholder="Contoh: 5000" min="0" required value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>">
                </div>
                <div class="form-grup">
                    <label>Stok:</label>
                    <input type="number" name="stok" class="input" placeholder="Contoh: 100" min="0" value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>">
                </div>
                <div class="form-grup">
                    <label>Unggah Gambar <small style="color:#888;">(opsional, maks 2MB)</small></label>
                    <input type="file" name="gambar" class="input" accept="image/*" style="padding:6px;">
                </div>
                <button type="submit" class="btn btn-biru">Simpan</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
