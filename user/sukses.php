<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: index.php"); exit; }
$id = (int)($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Pesanan Berhasil</title><link rel="stylesheet" href="../css/style.css"></head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#d6eaf8;">
<div class="kotak" style="text-align:center;max-width:400px;width:90%;margin:20px;">
    <div style="font-size:3.5rem;margin-bottom:12px;">&#9989;</div>
    <h2 style="color:#27ae60;margin-bottom:8px;">Pesanan Berhasil!</h2>
    <p style="color:#888;margin-bottom:20px;">Pesanan #<?= $id ?> sedang diproses.<br>Kamu bisa cek status di halaman Pesanan Saya.</p>
    <a href="pesanan_saya.php" class="btn btn-biru" style="margin-right:8px;">Lihat Pesanan</a>
    <a href="beranda.php" class="btn btn-abu">Belanja Lagi</a>
</div>
</body>
</html>
