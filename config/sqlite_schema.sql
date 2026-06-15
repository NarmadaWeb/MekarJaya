DROP TABLE IF EXISTS faq;
DROP TABLE IF EXISTS artikel;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS pengguna;

CREATE TABLE pengguna (
    pengguna_id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT CHECK(length(nama) <= 35) NOT NULL,
    email TEXT CHECK(length(email) <= 50) UNIQUE NOT NULL,
    password TEXT CHECK(length(password) <= 64) NOT NULL,
    peran TEXT CHECK(peran IN ('user', 'admin')) DEFAULT 'user',
    telepon TEXT CHECK(length(telepon) <= 15) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    foto_profil TEXT CHECK(length(foto_profil) <= 50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produk (
    produk_id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT CHECK(length(nama) <= 35) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    harga REAL NOT NULL,
    gambar TEXT CHECK(length(gambar) <= 50) DEFAULT NULL,
    kategori TEXT CHECK(length(kategori) <= 30) DEFAULT NULL,
    rating REAL DEFAULT 0.0,
    jumlah_ulasan INTEGER DEFAULT 0,
    stok INTEGER DEFAULT 100,
    unggulan INTEGER DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pesanan (
    pesanan_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pengguna_id INTEGER NOT NULL,
    total_harga REAL NOT NULL,
    status TEXT CHECK(status IN ('Pending', 'Processed', 'Shipped', 'Completed')) DEFAULT 'Pending',
    metode_pengiriman TEXT CHECK(length(metode_pengiriman) <= 20) DEFAULT 'Standard',
    metode_pembayaran TEXT CHECK(length(metode_pembayaran) <= 20) NOT NULL,
    alamat_pengiriman TEXT NOT NULL,
    bukti_pembayaran TEXT CHECK(length(bukti_pembayaran) <= 50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE detail_pesanan (
    detail_pesanan_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pesanan_id INTEGER NOT NULL,
    produk_id INTEGER NOT NULL,
    jumlah INTEGER NOT NULL,
    harga REAL NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE
);

CREATE TABLE artikel (
    artikel_id INTEGER PRIMARY KEY AUTOINCREMENT,
    judul TEXT CHECK(length(judul) <= 50) NOT NULL,
    kutipan TEXT DEFAULT NULL,
    konten TEXT DEFAULT NULL,
    kategori TEXT CHECK(length(kategori) <= 30) DEFAULT NULL,
    penulis TEXT CHECK(length(penulis) <= 35) DEFAULT NULL,
    gambar TEXT CHECK(length(gambar) <= 50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE faq (
    faq_id INTEGER PRIMARY KEY AUTOINCREMENT,
    kategori TEXT CHECK(length(kategori) <= 30) DEFAULT NULL,
    pertanyaan TEXT CHECK(length(pertanyaan) <= 100) NOT NULL,
    jawaban TEXT NOT NULL
);

CREATE TABLE pembayaran (
    pembayaran_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pesanan_id INTEGER NOT NULL,
    pengguna_id INTEGER NOT NULL,
    transaksi_id TEXT NOT NULL,
    tanggal_pembayaran TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE kategori (
    kategori_id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama_kategori TEXT NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifikasi (
    notifikasi_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pesanan_id INTEGER DEFAULT NULL,
    judul TEXT NOT NULL,
    pesan TEXT DEFAULT NULL,
    dibaca INTEGER DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE SET NULL
);
