<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

$id_pelanggan = (int)$_SESSION['id_pelanggan'];

// =========================================================
// SIMPAN ATAU UPDATE RATING (pelanggan hanya bisa rating order miliknya sendiri)
// =========================================================
if (isset($_POST['id_laundry']) && isset($_POST['rating'])) {
    $id_laundry = (int)$_POST['id_laundry'];
    $rating     = (int)$_POST['rating'];
    $komentar   = mysqli_real_escape_string($conn, trim($_POST['komentar'] ?? ''));

    if ($rating < 1 || $rating > 5) {
        header("Location: rating.php?notif=rating_invalid");
        exit();
    }

    // Pastikan order memang milik pelanggan yang login DAN sudah berstatus Selesai
    $cek_order = mysqli_query($conn, "
        SELECT Id_Laundry FROM Laundry 
        WHERE Id_Laundry = $id_laundry AND Id_Pelanggan = $id_pelanggan AND Status = 'Selesai'
    ");

    if (!$cek_order || mysqli_num_rows($cek_order) === 0) {
        header("Location: rating.php?notif=order_invalid");
        exit();
    }

    $check = mysqli_query($conn, "SELECT id_rating FROM ratings WHERE id_laundry = $id_laundry AND id_pelanggan = $id_pelanggan");

    if ($check && mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE ratings SET rating = $rating, komentar = '$komentar', updated_at = NOW() WHERE id_laundry = $id_laundry AND id_pelanggan = $id_pelanggan");
        header("Location: rating.php?notif=update_ok#order-$id_laundry");
    } else {
        mysqli_query($conn, "INSERT INTO ratings (id_laundry, id_pelanggan, rating, komentar) VALUES ($id_laundry, $id_pelanggan, $rating, '$komentar')");
        header("Location: rating.php?notif=tambah_ok#order-$id_laundry");
    }
    exit();
}

// =========================================================
// HAPUS RATING (hanya rating milik sendiri)
// =========================================================
if (isset($_GET['delete']) && isset($_GET['id_laundry'])) {
    $id_laundry = (int)$_GET['id_laundry'];
    mysqli_query($conn, "DELETE FROM ratings WHERE id_laundry = $id_laundry AND id_pelanggan = $id_pelanggan");
    header("Location: rating.php?notif=hapus_ok");
    exit();
}

// Akses tidak valid / tanpa data
header("Location: rating.php");
exit();
