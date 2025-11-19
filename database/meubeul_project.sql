
CREATE DATABASE IF NOT EXISTS meubeul_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE meubeul_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_produk VARCHAR(200) NOT NULL,
  deskripsi TEXT,
  harga INT DEFAULT 0,
  stok INT DEFAULT 0,
  gambar VARCHAR(255),
  kategori VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS edukasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  konten TEXT,
  gambar VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total INT NOT NULL,
  items TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender_id INT NOT NULL,
    sender_role ENUM('user','admin') DEFAULT 'user',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id),
    INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO users (nama,email,password,role) VALUES
('Admin Haris','admin@meubeul.test','Admin123','admin'),
('User Budi','user@meubeul.test','User1','user');

INSERT INTO produk (nama_produk,deskripsi,harga,stok,gambar,kategori) VALUES
('Kursi Kayu Jati Classic','Kursi jati solid finishing natural. Cocok ruang tamu.',1200000,10,'kursi1.jpg','Kursi'),
('Meja Makan Kayu Solid','Meja makan kayu solid 6 kursi, permukaan halus dan tahan lama.',3200000,5,'meja1.jpg','Meja');

INSERT INTO edukasi (judul,konten,gambar) VALUES
('Mengenal Kayu Jati','Kayu jati terkenal kuat dan tahan air...', 'edu_jati.jpg'),
('Ciri-ciri Kayu Solid Berkualitas','Serat rapat, warna konsisten, tahan lama...','edu_solid.jpg');