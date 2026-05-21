<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['peran'] != 'admin') {
    header("Location: ../user/index.php"); exit;
}
require_once '../koneksi.php';
$sukses = $error = '';

if (isset($_GET['hapus']) && isset($_GET['ok'])) {
    $id = (int)$_GET['hapus'];
    $g  = $conn->query("SELECT gambar FROM produk WHERE id=$id")->fetch_assoc();
    if ($g['gambar'] && file_exists("../uploads/".$g['gambar'])) unlink("../uploads/".$g['gambar']);
    $conn->query("DELETE FROM produk WHERE id=$id");
    header("Location: kelola.php?sukses=hapus"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id    = (int)$_POST['edit_id'];
    $nama  = trim($_POST['nama_produk']);
    $harga = (int)$_POST['harga'];
    $stok  = (int)$_POST['stok'];
    if (!$nama || $harga <= 0) {
        $error = "Nama dan harga wajib diisi!";
    } else {
        $gbr_sql = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','gif','webp']) && $_FILES['gambar']['size'] <= 2*1024*1024) {
                $folder = __DIR__ . '/../uploads/';
                if (!is_dir($folder)) mkdir($folder, 0777, true);
                $lama = $conn->query("SELECT gambar FROM produk WHERE id=$id")->fetch_assoc();
                if ($lama['gambar'] && file_exists($folder.$lama['gambar'])) unlink($folder.$lama['gambar']);
                $gbr = uniqid().'.'.$ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], $folder.$gbr);
                $gbr_sql = ", gambar='$gbr'";
            }
        }
        $conn->query("UPDATE produk SET nama_produk='$nama', harga=$harga, stok=$stok $gbr_sql WHERE id=$id");
        header("Location: kelola.php?sukses=edit"); exit;
    }
}

if (isset($_GET['sukses'])) {
    $sukses = $_GET['sukses'] == 'hapus' ? "Produk berhasil dihapus." : "Produk berhasil diperbarui.";
}

$edit_id        = (int)($_GET['edit'] ?? 0);
$konfirm_id     = (int)($_GET['konfirm'] ?? 0);
$edit_produk    = $edit_id    ? $conn->query("SELECT * FROM produk WHERE id=$edit_id")->fetch_assoc()   : null;
$konfirm_produk = $konfirm_id ? $conn->query("SELECT * FROM produk WHERE id=$konfirm_id")->fetch_assoc() : null;
$semua          = $conn->query("SELECT * FROM produk ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Produk - Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout-admin">
    <aside class="sidebar">
        <div class="brand">Toko Perlengkapan<br>Alat Tulis</div>
        <nav>
            <a href="index.php">+ Tambah Produk</a>
            <a href="kelola.php" class="aktif">Kelola Produk</a>
            <a href="pesanan.php">Daftar Pesanan</a>
            <a href="../user/logout.php" style="color:rgba(255,255,255,0.6);margin-top:20px;">Keluar</a>
        </nav>
    </aside>
    <main class="konten">
        <h1>Halaman Admin</h1>
        <div class="tab">
            <a href="index.php">Tambah Produk</a>
            <a href="kelola.php" class="aktif">Kelola Produk</a>
        </div>
        <?php if ($sukses): ?><div class="alert alert-hijau"><?= $sukses ?></div><?php endif; ?>
        <?php if ($error):  ?><div class="alert alert-merah"><?= $error ?></div><?php endif; ?>

        <?php if ($konfirm_produk): ?>
        <div class="kotak-konfirm">
            <h3 style="color:#e74c3c;margin-bottom:8px;">Konfirmasi Hapus</h3>
            <p>Yakin hapus produk <strong><?= htmlspecialchars($konfirm_produk['nama_produk']) ?></strong>?</p>
            <div style="display:flex;gap:8px;margin-top:10px;">
                <a href="kelola.php?hapus=<?= $konfirm_produk['id'] ?>&ok=1" class="btn btn-merah">Ya, Hapus</a>
                <a href="kelola.php" class="btn btn-abu">Batal</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($edit_produk): ?>
        <div class="kotak" style="max-width:460px;margin-bottom:22px;border:2px solid #85c1e9;">
            <h3 style="margin-bottom:14px;color:#2980b9;">Edit: <?= htmlspecialchars($edit_produk['nama_produk']) ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" value="<?= $edit_produk['id'] ?>">
                <div class="form-grup">
                    <label>Nama Produk:</label>
                    <input type="text" name="nama_produk" class="input" value="<?= htmlspecialchars($edit_produk['nama_produk']) ?>" required>
                </div>
                <div class="form-grup">
                    <label>Harga:</label>
                    <input type="number" name="harga" class="input" value="<?= $edit_produk['harga'] ?>" min="0" required>
                </div>
                <div class="form-grup">
                    <label>Stok:</label>
                    <input type="number" name="stok" class="input" value="<?= $edit_produk['stok'] ?>" min="0">
                </div>
                <div class="form-grup">
                    <label>Ubah Gambar (opsional)</label>
                    <?php if ($edit_produk['gambar'] && file_exists("../uploads/".$edit_produk['gambar'])): ?>
                    <img src="../uploads/<?= $edit_produk['gambar'] ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px;display:block;margin-bottom:6px;">
                    <?php endif; ?>
                    <input type="file" name="gambar" class="input" accept="image/*" style="padding:6px;">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-biru">Simpan</button>
                    <a href="kelola.php" class="btn btn-abu">Batal</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <h3 style="margin-bottom:14px;">Kelola Produk</h3>
        <div class="tabel-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Nama Produk</th><th>Stok</th><th>Harga</th><th>Gambar</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($semua as $i => $p): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td style="font-weight:bold;"><?= htmlspecialchars($p['nama_produk']) ?></td>
                <td style="color:<?= $p['stok']<10?'#e74c3c':'#27ae60' ?>;font-weight:bold;"><?= $p['stok'] ?></td>
                <td>Rp <?= number_format($p['harga'],0,',','.') ?></td>
                <td>
                    <?php if ($p['gambar'] && file_exists("../uploads/".$p['gambar'])): ?>
                    <img src="../uploads/<?= $p['gambar'] ?>" style="width:40px;height:40px;object-fit:cover;border-radius:5px;">
                    <?php else: ?><span style="color:#ccc;">—</span><?php endif; ?>
                </td>
                <td>
                    <a href="kelola.php?edit=<?= $p['id'] ?>" class="btn btn-abu" style="font-size:0.8rem;padding:5px 10px;">Edit</a>
                    <a href="kelola.php?konfirm=<?= $p['id'] ?>" class="btn btn-merah" style="font-size:0.8rem;padding:5px 10px;">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($semua)): ?>
            <tr><td colspan="6" style="text-align:center;color:#888;padding:24px;">Belum ada produk.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </main>
</div>
</body>
</html>
