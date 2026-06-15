-- Drop Database and Recreate to apply clean schema
DROP DATABASE IF EXISTS madu;
CREATE DATABASE madu;
USE madu;

-- Tabel pengguna (sebelumnya: users)
CREATE TABLE pengguna (
    pengguna_id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(35) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(64) NOT NULL,
    peran ENUM('user', 'admin') DEFAULT 'user',
    telepon VARCHAR(15) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    foto_profil VARCHAR(50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel produk (sebelumnya: products)
CREATE TABLE produk (
    produk_id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(35) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    gambar VARCHAR(50) DEFAULT NULL,
    kategori VARCHAR(30) DEFAULT NULL,
    rating DECIMAL(3, 1) DEFAULT 0.0,
    jumlah_ulasan INT DEFAULT 0,
    stok INT DEFAULT 100,
    unggulan TINYINT(1) DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel pesanan (sebelumnya: orders)
CREATE TABLE pesanan (
    pesanan_id INT AUTO_INCREMENT PRIMARY KEY,
    pengguna_id INT NOT NULL,
    total_harga DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Processed', 'Shipped', 'Completed') DEFAULT 'Pending',
    metode_pengiriman VARCHAR(20) DEFAULT 'Standard',
    metode_pembayaran VARCHAR(20) NOT NULL,
    alamat_pengiriman TEXT NOT NULL,
    bukti_pembayaran VARCHAR(50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

-- Tabel detail_pesanan (sebelumnya: order_items)
CREATE TABLE detail_pesanan (
    detail_pesanan_id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE
);

-- Tabel artikel (sebelumnya: blog_posts)
CREATE TABLE artikel (
    artikel_id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(50) NOT NULL,
    kutipan TEXT DEFAULT NULL,
    konten TEXT DEFAULT NULL,
    kategori VARCHAR(30) DEFAULT NULL,
    penulis VARCHAR(35) DEFAULT NULL,
    gambar VARCHAR(50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE faq (
    faq_id INT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(30) DEFAULT NULL,
    pertanyaan VARCHAR(100) NOT NULL,
    jawaban TEXT NOT NULL
);

-- Tabel pembayaran (untuk mencatat log transaksi)
CREATE TABLE pembayaran (
    pembayaran_id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    pengguna_id INT NOT NULL,
    transaksi_id VARCHAR(50) NOT NULL,
    tanggal_pembayaran TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

-- Tabel kategori
CREATE TABLE kategori (
    kategori_id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(30) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel notifikasi
CREATE TABLE notifikasi (
    notifikasi_id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT DEFAULT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT DEFAULT NULL,
    dibaca TINYINT(1) DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE SET NULL
);
