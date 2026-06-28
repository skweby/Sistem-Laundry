<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

$id_pelanggan = (int)$_SESSION['id_pelanggan'];

// =========================================================
// HAPUS RATING
// =========================================================
if (isset($_GET['delete']) && isset($_GET['id_laundry'])) {
    $id_laundry = (int)$_GET['id_laundry'];

    // Pastikan rating milik pelanggan ini
    $check = mysqli_query($conn, "SELECT Id_Rating FROM rating WHERE Id_Laundry = $id_laundry AND Id_Pelanggan = $id_pelanggan");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM rating WHERE Id_Laundry = $id_laundry AND Id_Pelanggan = $id_pelanggan");
        header("Location: rating.php?notif=hapus_ok&top=1");
    } else {
        header("Location: rating.php?notif=order_invalid&top=1");
    }
    exit();
}

// =========================================================
// TAMBAH / UPDATE RATING
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_laundry = isset($_POST['id_laundry']) ? (int)$_POST['id_laundry'] : 0;
    $rating     = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $komentar   = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';

    if ($rating < 1 || $rating > 5) {
        header("Location: rating.php?notif=rating_invalid&top=1");
        exit();
    }

    // Cek apakah order milik pelanggan dan status Selesai
    $check_order = mysqli_query($conn, "
        SELECT Id_Laundry FROM laundry 
        WHERE Id_Laundry = $id_laundry AND Id_Pelanggan = $id_pelanggan AND Status = 'Selesai'
    ");
    if (mysqli_num_rows($check_order) == 0) {
        header("Location: rating.php?notif=order_invalid&top=1");
        exit();
    }

    // Escape komentar untuk keamanan
    $komentar_esc = mysqli_real_escape_string($conn, $komentar);

    // Cek apakah sudah ada rating untuk order ini
    $check_rating = mysqli_query($conn, "SELECT Id_Rating FROM rating WHERE Id_Laundry = $id_laundry AND Id_Pelanggan = $id_pelanggan");

    if (mysqli_num_rows($check_rating) > 0) {
        // Update
        $update = mysqli_query($conn, "
            UPDATE rating 
            SET Jumlah_Bintang = $rating, Komentar = '$komentar_esc', Tanggal_Rating = NOW()
            WHERE Id_Laundry = $id_laundry AND Id_Pelanggan = $id_pelanggan
        ");
        if ($update) {
            header("Location: rating.php?notif=update_ok&top=1");
        } else {
            // Jika gagal, tetap redirect dengan pesan error umum
            header("Location: rating.php?notif=rating_invalid&top=1");
        }
    } else {
        // Insert
        $insert = mysqli_query($conn, "
            INSERT INTO rating (Id_Laundry, Id_Pelanggan, Jumlah_Bintang, Komentar, Tanggal_Rating)
            VALUES ($id_laundry, $id_pelanggan, $rating, '$komentar_esc', NOW())
        ");
        if ($insert) {
            header("Location: rating.php?notif=tambah_ok&top=1");
        } else {
            header("Location: rating.php?notif=rating_invalid&top=1");
        }
    }
    exit();
}

// Jika tidak ada aksi, kembalikan ke rating
header("Location: rating.php?top=1");
exit();
?>