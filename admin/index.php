<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: ../login_admin.php");
    exit();
}

// Query dashboard (sama seperti sebelumnya)
$hari_ini = date('Y-m-d');
$q_order = mysqli_query($conn, "SELECT COUNT(*) as total FROM Laundry WHERE Tanggal_Masuk = '$hari_ini'");
$r_order = mysqli_fetch_assoc($q_order);

$q_proses = mysqli_query($conn, "SELECT COUNT(*) as total FROM Laundry WHERE Status NOT IN ('Selesai', 'Pending')");
$r_proses = mysqli_fetch_assoc($q_proses);

$q_lunas = mysqli_query($conn, "SELECT COUNT(*) as total FROM Laundry WHERE Status != 'Selesai'");
$r_lunas = mysqli_fetch_assoc($q_lunas);

$q_omset = mysqli_query($conn, "SELECT SUM(Total) as total FROM Laundry WHERE Status = 'Selesai' AND Tanggal_Masuk = '$hari_ini'");
$r_omset = mysqli_fetch_assoc($q_omset);
$omset = $r_omset['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RIFFANASH LAUNDRY</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== STYLE SIDEBAR & MAIN ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 32px; color: #1E293B; }
        .brand i { color: #0066FF; font-size: 22px; }
        .menu-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .menu-item.active a { background: #0066FF; color: white; }
        .menu-item a:hover:not(.active a) { background: #F1F5F9; color: #1E293B; }
        .btn-logout { background: #FFE4E6; color: #E11D48; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: block; margin-top: auto; }
        .main-content { flex: 1; padding: 40px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .user-profile { display: flex; align-items: center; gap: 10px; font-weight: 600; color: #1E293B; }
        .banner-blue { background: linear-gradient(135deg, #2563EB, #1D4ED8); color: white; padding: 32px; border-radius: 20px; margin-bottom: 32px; }
        .banner-blue h1 { font-size: 24px; font-weight: 700; }
        .badge-open { background: #22C55E; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; margin-top: 8px; }
        .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .card-stat { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; }
        .card-stat span { font-size: 12px; color: #64748B; font-weight: 700; text-transform: uppercase; }
        .card-stat h2 { font-size: 28px; color: #1E293B; font-weight: 700; margin-top: 8px; }
        .table-container { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; }
        .table-container h3 { font-size: 16px; color: #1E293B; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px; color: #64748B; font-size: 13px; font-weight: 600; border-bottom: 1px solid #E2E8F0; }
        td { padding: 16px 12px; font-size: 14px; color: #334155; border-bottom: 1px solid #F1F5F9; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-badge.selesai { background: #DCFCE7; color: #166534; }
        .status-badge.proses { background: #FEF9C3; color: #854D0E; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <p style="color: #64748B; font-size: 14px;">Halaman Admin</p>
                <h2 style="color: #1E293B; font-size: 18px; font-weight: 700;"><?php echo date('d F Y'); ?></h2>
            </div>
            <div class="user-profile">
                <i class="fa-solid fa-circle-user" style="font-size: 24px; color: #64748B;"></i>
                <span><?php echo $_SESSION['nama_user']; ?></span>
                <span style="font-size:11px; background:#E0F2FE; color:#0369A1; padding:2px 10px; border-radius:12px;"><?php echo ucfirst($_SESSION['role']); ?></span>
            </div>
        </div>

        <div class="banner-blue">
            <p>👋 Selamat Siang, <?php echo $_SESSION['nama_user']; ?>!</p>
            <h1>RIFFANASH LAUNDRY</h1>
            <p style="font-size: 14px; opacity: 0.9;">Jam Operasional: 08:00 - 20:00 WIB</p>
            <div class="badge-open">● TOKO BUKA</div>
        </div>

        <div class="cards-grid">
            <div class="card-stat" style="border-left: 4px solid #3B82F6;">
                <span>Order Hari Ini</span>
                <h2><?php echo $r_order['total']; ?></h2>
            </div>
            <div class="card-stat" style="border-left: 4px solid #EAB308;">
                <span>Dalam Proses</span>
                <h2><?php echo $r_proses['total']; ?></h2>
            </div>
            <div class="card-stat" style="border-left: 4px solid #EF4444;">
                <span>Belum Lunas</span>
                <h2><?php echo $r_lunas['total']; ?></h2>
            </div>
            <div class="card-stat" style="border-left: 4px solid #10B981;">
                <span>Omset Hari Ini</span>
                <h2>Rp <?php echo number_format($omset, 0, ',', '.'); ?></h2>
            </div>
        </div>

        <div class="table-container">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Transaksi Terakhir</h3>
            <table>
                <thead>
                    <tr>
                        <th>PELANGGAN</th>
                        <th>TANGGAL MASUK</th>
                        <th>STATUS</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_recent = mysqli_query($conn, "SELECT L.*, P.Nama FROM Laundry L JOIN Pelanggan P ON L.Id_Pelanggan = P.IdPelanggan ORDER BY L.Id_Laundry DESC LIMIT 5");
                    if (mysqli_num_rows($q_recent) == 0) {
                        echo "<tr><td colspan='4' style='text-align:center;'>Belum ada data transaksi masuk.</td></tr>";
                    }
                    while($row = mysqli_fetch_assoc($q_recent)) {
                        $badge_class = ($row['Status'] == 'Selesai' || $row['Status'] == 'Diantar') ? 'selesai' : 'proses';
                        echo "<tr>
                            <td><strong>{$row['Nama']}</strong></td>
                            <td>".date('d M Y', strtotime($row['Tanggal_Masuk']))."</td>
                            <td><span class='status-badge {$badge_class}'>{$row['Status']}</span></td>
                            <td>Rp ".number_format($row['Total'], 0, ',', '.')."</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>