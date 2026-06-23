<?php
// config.php
$host = "localhost";
$user = "root";     
$pass = "";         
$db   = "ilham_laundry";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// =========================================================
// AUTO-CREATE TABEL PENDUKUNG FITUR RATING & CHAT STATUS
// (idempotent - aman dijalankan berkali-kali, tidak perlu import SQL manual)
// =========================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS ratings (
    id_rating INT AUTO_INCREMENT PRIMARY KEY,
    id_laundry INT NOT NULL,
    id_pelanggan INT NOT NULL,
    rating TINYINT NOT NULL,
    komentar TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_rating_per_order (id_laundry, id_pelanggan),
    INDEX idx_rating_pelanggan (id_pelanggan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS chat_status (
    id_chat INT AUTO_INCREMENT PRIMARY KEY,
    id_laundry INT NOT NULL,
    id_pelanggan INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    pesan TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_chat_pelanggan (id_pelanggan),
    INDEX idx_chat_laundry (id_laundry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
?>