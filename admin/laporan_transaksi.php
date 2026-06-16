<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

// Filter tanggal
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// Query pembayaran dengan join ke laundry dan pelanggan
$query = "SELECT p.*, l.Total, l.Status as status_laundry, pl.Nama as nama_pelanggan 
          FROM pembayaran p
          JOIN laundry l ON p.Id_Laundry = l.Id_Laundry
          JOIN pelanggan pl ON l.Id_Pelanggan = pl.IdPelanggan
          WHERE DATE(p.Tanggal_Bayar) BETWEEN '$tgl_awal' AND '$tgl_akhir'
          ORDER BY p.Tanggal_Bayar DESC";

$result = mysqli_query($conn, $query);

$total_pendapatan = 0;
$data_pembayaran = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data_pembayaran[] = $row;
    if ($row['Status_Bayar'] == 'Lunas') {
        $total_pendapatan += $row['Total_Bayar'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - Admin</title>
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
        .filter-box { background: white; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 24px; }
        .filter-box label { font-size: 13px; font-weight: 600; color: #475569; }
        .filter-box input { padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 8px; }
        .filter-box button { padding: 8px 20px; background: #0066FF; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .total-card { background: white; padding: 16px 24px; border-radius: 12px; border-left: 4px solid #10B981; margin-bottom: 20px; }
        .total-card span { font-size: 14px; color: #475569; }
        .total-card h2 { font-size: 24px; color: #1E293B; margin-top: 4px; }
        .table-container { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px; color: #64748B; font-size: 13px; font-weight: 600; border-bottom: 1px solid #E2E8F0; }
        td { padding: 12px; font-size: 14px; border-bottom: 1px solid #F1F5F9; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-lunas { background: #DCFCE7; color: #166534; }
        .status-belum { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand"><i class="fa-solid fa-soap"></i><span>ILHAM LAUNDRY</span></div>
    <ul class="menu-list">
        <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
        <li class="menu-item"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
        <li class="menu-item active"><a href="laporan_transaksi.php"><i class="fa-solid fa-coins"></i> Laporan Transaksi</a></li>
        <li class="menu-item"><a href="manajemen_stok.php"><i class="fa-solid fa-boxes-stacked"></i> Manajemen Stok</a></li>
        <li class="menu-item"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
    </ul>
</div>

<div class="main-content">
    <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-coins"></i> Laporan Transaksi Pembayaran</h2>

    <form method="GET" class="filter-box">
        <div>
            <label>Tanggal Awal</label><br>
            <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>">
        </div>
        <div>
            <label>Tanggal Akhir</label><br>
            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>">
        </div>
        <button type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
    </form>

    <div class="total-card">
        <span>Total Pendapatan (Lunas) Periode</span>
        <h2>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Bayar</th>
                    <th>Pelanggan</th>
                    <th>ID Laundry</th>
                    <th>Total Bayar</th>
                    <th>Tanggal Bayar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data_pembayaran) > 0): ?>
                    <?php foreach ($data_pembayaran as $row): ?>
                    <tr>
                        <td>#<?= $row['Id_Pembayaran'] ?></td>
                        <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                        <td><?= $row['Id_Laundry'] ?></td>
                        <td>Rp <?= number_format($row['Total_Bayar'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($row['Tanggal_Bayar'])) ?></td>
                        <td><span class="status-badge <?= $row['Status_Bayar'] == 'Lunas' ? 'status-lunas' : 'status-belum' ?>"><?= $row['Status_Bayar'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 30px;">Tidak ada data pembayaran pada periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>