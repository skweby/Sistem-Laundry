<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: ../login.php");
    exit();
}

// Filter tanggal (default: bulan ini)
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// Query ambil data order yang sudah SELESAI, dengan join pelanggan dan (opsional) pembayaran
$query = "SELECT 
            l.Id_Laundry,
            l.Tanggal_Masuk,
            l.Tanggal_Keluar,
            l.Total,
            l.Status,
            pl.Nama AS nama_pelanggan,
            p.Id_Pembayaran,
            p.Tanggal_Bayar,
            p.Total_Bayar,
            p.Status_Bayar
          FROM laundry l
          JOIN pelanggan pl ON l.Id_Pelanggan = pl.IdPelanggan
          LEFT JOIN pembayaran p ON l.Id_Laundry = p.Id_Laundry
          WHERE l.Status = 'Selesai'
            AND DATE(l.Tanggal_Masuk) BETWEEN '$tgl_awal' AND '$tgl_akhir'
          ORDER BY l.Tanggal_Masuk DESC";

$result = mysqli_query($conn, $query);

$total_pendapatan = 0;
$data_transaksi = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Total pendapatan diambil dari Total order (anggap lunas)
    $total_pendapatan += $row['Total'];
    // Jika tidak ada pembayaran, kita isi status bayar dengan 'Lunas (Otomatis)'
    if (is_null($row['Status_Bayar'])) {
        $row['Status_Bayar'] = 'Lunas (Otomatis)';
        $row['Tanggal_Bayar'] = $row['Tanggal_Keluar'] ?? $row['Tanggal_Masuk']; // fallback
    }
    $data_transaksi[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }
        
        /* ── SIDEBAR ── */
        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 32px; color: #1E293B; }
        .brand i { color: #0066FF; font-size: 22px; }
        .menu-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .menu-item.active a { background: #0066FF; color: white; }
        .menu-item a:hover:not(.active a) { background: #F1F5F9; color: #1E293B; }
        .btn-logout { background: #FFE4E6; color: #E11D48; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: block; }

        .main-content { flex: 1; padding: 40px; }
        .page-title { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 6px; }
        .page-sub   { font-size: 13px; color: #64748B; margin-bottom: 24px; }

        .filter-box { background: white; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 24px; }
        .filter-box label { font-size: 13px; font-weight: 600; color: #475569; }
        .filter-box input { padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 8px; }
        .filter-box button { padding: 8px 20px; background: #0066FF; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .filter-box button:hover { background: #0052CC; }

        .total-card { background: white; padding: 16px 24px; border-radius: 12px; border-left: 4px solid #10B981; margin-bottom: 20px; }
        .total-card span { font-size: 14px; color: #475569; }
        .total-card h2 { font-size: 24px; color: #1E293B; margin-top: 4px; }

        .table-container { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; }
        .table-container h3 { font-size: 16px; color: #1E293B; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px; color: #64748B; font-size: 13px; font-weight: 600; border-bottom: 2px solid #E2E8F0; }
        td { padding: 12px; font-size: 14px; border-bottom: 1px solid #F1F5F9; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-lunas { background: #DCFCE7; color: #166534; }
        .status-belum { background: #FEE2E2; color: #991B1B; }
        .status-otomatis { background: #F3E8FF; color: #7C3AED; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <p class="page-title"><i class="fa-solid fa-coins"></i> Laporan Transaksi</p>
    <p class="page-sub">Menampilkan semua order dengan status <strong>SELESAI</strong> beserta rekap pendapatan.</p>

    <form method="GET" class="filter-box">
        <div>
            <label>Tanggal Awal</label><br>
            <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
        </div>
        <div>
            <label>Tanggal Akhir</label><br>
            <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
        </div>
        <button type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
    </form>

    <div class="total-card">
        <span>Total Pendapatan (Order Selesai)</span>
        <h2>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
    </div>

    <div class="table-container">
        <h3><i class="fa-solid fa-list-ul"></i> Daftar Order Selesai</h3>
        <table>
            <thead>
                <tr>
                    <th>ID Order</th>
                    <th>Pelanggan</th>
                    <th>Tanggal Masuk</th>
                    <th>Tanggal Selesai</th>
                    <th>Total (Rp)</th>
                    <th>Status Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data_transaksi) > 0): ?>
                    <?php foreach ($data_transaksi as $row):
                        $badge_class = 'status-lunas';
                        if ($row['Status_Bayar'] == 'Belum Lunas') $badge_class = 'status-belum';
                        elseif ($row['Status_Bayar'] == 'Lunas (Otomatis)') $badge_class = 'status-otomatis';
                    ?>
                    <tr>
                        <td><strong>#<?= $row['Id_Laundry'] ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= date('d M Y', strtotime($row['Tanggal_Masuk'])) ?></td>
                        <td><?= !empty($row['Tanggal_Keluar']) ? date('d M Y', strtotime($row['Tanggal_Keluar'])) : '-' ?></td>
                        <td style="font-weight:700; color:#0066FF;">Rp <?= number_format($row['Total'], 0, ',', '.') ?></td>
                        <td><span class="status-badge <?= $badge_class ?>"><?= htmlspecialchars($row['Status_Bayar']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 30px; color:#94A3B8;">
                        <i class="fa-solid fa-inbox" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                        Belum ada order dengan status SELESAI pada periode ini.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>