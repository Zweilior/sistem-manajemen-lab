CREATE DATABASE IF NOT EXISTS laboratorium;

USE laboratorium;

DROP TABLE IF EXISTS transaksi_stok;
DROP TABLE IF EXISTS stok_lab;
DROP TABLE IF EXISTS item_alat_bahan;
DROP TABLE IF EXISTS kategori_item;

CREATE TABLE IF NOT EXISTS admin (
    id_admin   INT PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kategori_item (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT
) AUTO_INCREMENT = 11;

CREATE TABLE item_alat_bahan (
    id_item INT PRIMARY KEY AUTO_INCREMENT,
    nama_item VARCHAR(255) NOT NULL,
    id_kategori INT NOT NULL,
    satuan VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    FOREIGN KEY (id_kategori) REFERENCES kategori_item(id_kategori)
) AUTO_INCREMENT = 221;

CREATE TABLE stok_lab (
    id_stok INT PRIMARY KEY AUTO_INCREMENT,
    id_item INT NOT NULL,
    jumlah INT NOT NULL,
    kondisi VARCHAR(100),
    tanggal_update TIMESTAMP,
    FOREIGN KEY (id_item) REFERENCES item_alat_bahan(id_item)
) AUTO_INCREMENT = 111;

CREATE TABLE transaksi_stok (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    id_stok INT NOT NULL,
    jenis_transaksi VARCHAR(50) NOT NULL,
    jumlah INT NOT NULL,
    tanggal_transaksi DATETIME,
    keterangan TEXT,
    FOREIGN KEY (id_stok) REFERENCES stok_lab(id_stok)
) AUTO_INCREMENT = 1;


INSERT INTO admin (username, password)
VALUES (
    'admin',
    '$2y$10$YOdR7m3Kq5i9dQhFidy1e.XQGhmVJSfyFW8rJzT6dZkxIvFYsOqKi'  -- hash dari 'rahasia'
);

UPDATE admin
SET password = '$2y$10$YOdR7m3Kq5i9dQhFidy1e.XQGhmVJSfyFW8rJzT6dZkxIvFYsOqKi'
WHERE username = 'admin';

INSERT INTO kategori_item (nama_kategori, deskripsi) 
VALUES 
('Bahan - Asam', 'Bahan kimia dengan sifat asam'),
('Bahan - Basa', 'Bahan kimia dengan sifat basa'),
('Bahan - Netral & Garam', 'Bahan kimia netral dan garam-garaman'),
('Bahan - Pelarut', 'Pelarut organik dan anorganik'),
('Bahan - Indikator', 'Bahan untuk indikator titrasi atau pH'),
('Peralatan Gelas', 'Alat lab berbahan dasar gelas'),
('Peralatan Umum', 'Alat bantu non-gelas dan non-instrumen'),
('Instrumen Lab', 'Peralatan elektronik dan alat pengukuran');

INSERT INTO item_alat_bahan (nama_item, id_kategori, satuan, deskripsi) 
VALUES 
('Beaker Glass', 16, 'buah', '500ml'),
('Labu Ukur', 16, 'buah', '250ml'),
('Erlenmeyer', 16, 'buah', '250ml'),
('Tabung Reaksi', 16, 'buah', 'Standard'),
('Kaca Arloji', 16, 'buah', 'Diameter 10cm'),
('Buret', 16, 'buah', '50ml'),
('Pipet Tetes', 16, 'buah', 'Kaca 3ml'),
('Spatula', 17, 'buah', 'Logam'),
('Batang Pengaduk', 17, 'buah', 'Kaca 20cm'),
('Filter Kertas', 17, 'box', 'Whatman No. 1'),
('Desikator', 17, 'buah', 'Vakum, 250mm'),
('Botol Semprot', 17, 'buah', 'Plastik 500ml'),
('Hot Plate', 18, 'unit', 'Termasuk Magnetic Stirrer'),
('Timbangan Analitik', 18, 'unit', 'Akurasi 0.001g'),
('Thermometer', 18, 'buah', 'Digital -50 s/d 150 C'),
('Asam Klorida 36%', 11, 'botol', '500ml'),
('Natrium Hidroksida', 12, 'botol', '500g, Pellet'),
('Akuades', 13, 'botol', '1L'),
('Kalium Klorida', 13, 'botol', '250g, Padat (Kristal)'),
('Natrium Asetat', 13, 'botol', '500g, Padat'),
('Natrium Klorida', 13, 'botol', '500g, Padat (Kristal)'),
('Natrium Oksalat', 13, 'botol', '250g, Padat'),
('Metanol', 14, 'botol', '1L, PA'),
('Etanol 95%', 14, 'botol', '1L'),
('Metilen Biru', 15, 'botol', '100ml, Indikator'),
('Fenolftalein', 15, 'botol', '100ml, Indikator');

INSERT INTO stok_lab (id_item, jumlah, kondisi, tanggal_update) 
VALUES 
(221, 30, 'Baik', NOW()),
(222, 25, 'Baik', NOW()),
(223, 30, 'Baik', NOW()),
(224, 100, 'Baik', NOW()),
(225, 40, 'Baik', NOW()),
(226, 15, 'Baik', NOW()),
(227, 50, 'Baik', NOW()),
(228, 20, 'Baik', NOW()),
(229, 30, 'Baik', NOW()),
(230, 10, 'Baik', NOW()),
(231, 5, 'Baik', NOW()),
(232, 20, 'Baik', NOW()),
(233, 8, 'Baik', NOW()),
(234, 3, 'Baik', NOW()),
(235, 10, 'Baik', NOW()),
(236, 5, 'Baik', NOW()),
(237, 10, 'Baik', NOW()),
(238, 20, 'Baik', NOW()),
(239, 10, 'Baik', NOW()),
(240, 8, 'Baik', NOW()),
(241, 15, 'Baik', NOW()),
(242, 6, 'Baik', NOW()),
(243, 12, 'Baik', NOW()),
(244, 12, 'Baik', NOW()),
(245, 10, 'Baik', NOW()),
(246, 8, 'Baik', NOW());

INSERT INTO transaksi_stok (id_stok, jenis_transaksi, jumlah, tanggal_transaksi, keterangan) 
VALUES 
(111, 'Masuk', 30, NOW(), 'Pengadaan awal'),
(112, 'Masuk', 25, NOW(), 'Pengadaan awal'),
(113, 'Masuk', 30, NOW(), 'Pengadaan awal'),
(114, 'Masuk', 100, NOW(), 'Pengadaan awal'),
(115, 'Masuk', 40, NOW(), 'Pengadaan awal'),
(116, 'Masuk', 15, NOW(), 'Pengadaan awal'),
(117, 'Masuk', 50, NOW(), 'Pengadaan awal'),
(118, 'Masuk', 20, NOW(), 'Pengadaan awal'),
(119, 'Masuk', 30, NOW(), 'Pengadaan awal'),
(120, 'Masuk', 10, NOW(), 'Pengadaan awal'),
(121, 'Masuk', 5, NOW(), 'Pengadaan awal'),
(122, 'Masuk', 20, NOW(), 'Pengadaan awal'),
(123, 'Masuk', 8, NOW(), 'Pengadaan awal'),
(124, 'Masuk', 3, NOW(), 'Pengadaan awal'),
(125, 'Masuk', 10, NOW(), 'Pengadaan awal'),
(126, 'Masuk', 5, NOW(), 'Pengadaan awal'),
(127, 'Masuk', 10, NOW(), 'Pengadaan awal'),
(128, 'Masuk', 20, NOW(), 'Pengadaan awal'),
(129, 'Masuk', 10, NOW(), 'Pengadaan awal'),
(130, 'Masuk', 8, NOW(), 'Pengadaan awal'),
(131, 'Masuk', 15, NOW(), 'Pengadaan awal'),
(132, 'Masuk', 6, NOW(), 'Pengadaan awal'),
(133, 'Masuk', 12, NOW(), 'Pengadaan awal'),
(134, 'Masuk', 12, NOW(), 'Pengadaan awal'),
(135, 'Masuk', 10, NOW(), 'Pengadaan awal'),
(136, 'Masuk', 8, NOW(), 'Pengadaan awal');