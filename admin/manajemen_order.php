<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

// Proses Update Status Order jika form dikirim
if (isset($_POST['update_status'])) {
    $id_laundry = $_POST['id_laundry'];
    $status_baru = $_POST['status_laundry'];
    
    mysqli_query($conn, "UPDATE Laundry SET Status = '$status_baru' WHERE Id_Laundry = '$id_laundry'");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Order - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Desain layout reusable sidebar dari index.php */
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
        select { padding: 6px; border-radius: 6px; border: 1px solid #CBD5E1; font-weight: 600; }
        .btn-update { padding: 6px 12px; background: #0066FF; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand"><i class="fa-solid fa-soap"></i><span>ILHAM LAUNDRY</span></div>
        <ul class="menu-list">
            <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item active"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
            <li class="menu-item"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
            <li class="menu-item"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="table-container">
            <h2><i class="fa-solid fa-list-check"></i> Antrean Manajemen Order</h2>
            <table>
                <thead>
                    <tr>
                        <th>NAMA PELANGGAN</th>
                        <th>ALAMAT</th>
                        <th>TANGGAL MASUK</th>
                        <th>STATUS SEKARANG</th>
                        <th>AKSI UBAH STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($conn, "SELECT L.*, P.Nama, P.Alamat FROM Laundry L JOIN Pelanggan P ON L.Id_Pelanggan = P.IdPelanggan ORDER BY L.Id_Laundry DESC");
                    while ($row = mysqli_fetch_assoc($query)) {
                        $statuses = ['Diterima', 'Pickup', 'Proses Cuci', 'Proses Setrika', 'Selesai Proses', 'Diantar', 'Selesai'];
                        echo "<tr>
                            <td><strong>{$row['Nama']}</strong></td>
                            <td>{$row['Alamat']}</td>
                            <td>{$row['Tanggal_Masuk']}</td>
                            <td><span style='color:#0066FF; font-weight:700;'>{$row['Status']}</span></td>
                            <td>
                                <form action='' method='POST' style='display:flex; gap:6px;'>
                                    <input type='hidden' name='id_laundry' value='{$row['Id_Laundry']}'>
                                    <select name='status_laundry'>";
                                    foreach ($statuses as $st) {
                                        $selected = ($row['Status'] == $st) ? 'selected' : '';
                                        echo "<option value='$st' $selected>$st</option>";
                                    }
                                    echo "</select>
                                    <button type='submit' name='update_status' class='btn-update'>Simpan</button>
                                </form>
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