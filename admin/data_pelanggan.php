<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

// Proses Hapus Pelanggan
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM Pelanggan WHERE IdPelanggan = '$id_hapus'");
    header("Location: data_pelanggan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 32px; color: #1E293B; }
        .menu-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; font-size: 14px; }
        .menu-item.active a { background: #0066FF; color: white; }
        .main-content { flex: 1; padding: 40px; }
        .table-container { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; }
        table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #E2E8F0; font-size: 14px; }
        .btn-danger { padding: 6px 12px; background: #EF4444; color: white; border: none; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand"><i class="fa-solid fa-soap"></i><span>ILHAM LAUNDRY</span></div>
        <ul class="menu-list">
            <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
            <li class="menu-item active"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
            <li class="menu-item"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="table-container">
            <h2><i class="fa-solid fa-users"></i> Data Manajemen Pelanggan</h2>
            <table>
                <thead>
                    <tr>
                        <th>NAMA</th>
                        <th>EMAIL</th>
                        <th>NO HP</th>
                        <th>ALAMAT</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ambil properti Pelanggan dari struktur diagrammu
                    $query = mysqli_query($conn, "SELECT IdPelanggan, Nama, Email, NoTelp, Alamat FROM Pelanggan ORDER BY IdPelanggan DESC");
                    while($row = mysqli_fetch_assoc($query)) {
                        echo "<tr>
                            <td><strong>{$row['Nama']}</strong></td>
                            <td>{$row['Email']}</td>
                            <td>{$row['NoTelp']}</td>
                            <td>{$row['Alamat']}</td>
                            <td>
                                <a href='data_pelanggan.php?hapus={$row['IdPelanggan']}' class='btn-danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus pelanggan ini?\")'><i class='fa-solid fa-trash'></i> Hapus</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>