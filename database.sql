CREATE DATABASE IF NOT EXISTS ecom;
USE ecom;

CREATE TABLE IF NOT EXISTS pengguna (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    sandi VARCHAR(100) NOT NULL,
    peran ENUM('pelanggan','admin') DEFAULT 'pelanggan'
);

CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(150) NOT NULL,
    harga INT NOT NULL,
    stok INT DEFAULT 0,
    gambar VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS alamat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengguna_id INT NOT NULL,
    nama_penerima VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    alamat_lengkap TEXT NOT NULL,
    kota VARCHAR(100) NOT NULL,
    kode_pos VARCHAR(10) NOT NULL,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
);

CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengguna_id INT NOT NULL,
    alamat_id INT NOT NULL,
    pembayaran VARCHAR(50) NOT NULL,
    total INT NOT NULL,
    status ENUM('menunggu','perlu_dikirim','sedang_dikirim','selesai') DEFAULT 'menunggu',
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(id),
    FOREIGN KEY (alamat_id) REFERENCES alamat(id)
);

CREATE TABLE IF NOT EXISTS detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga INT NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id),
    FOREIGN KEY (produk_id) REFERENCES produk(id)
);

CREATE TABLE IF NOT EXISTS keranjang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengguna_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT DEFAULT 1,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(id),
    FOREIGN KEY (produk_id) REFERENCES produk(id)
);

INSERT INTO pengguna (nama, sandi, peran) VALUES ('Admin', 'admin123', 'admin');

INSERT INTO produk (nama_produk, harga, stok) VALUES
('Buku Tulis Besar', 5000, 72),
('Buku Tulis Kecil', 3000, 80),
('Pena Standar', 3000, 112),
('Pena Hitam Joyko', 6000, 103),
('Pensil', 2000, 124),
('Penghapus', 2000, 90),
('Penggaris', 4000, 55),
('Tipp-ex', 5000, 40);
