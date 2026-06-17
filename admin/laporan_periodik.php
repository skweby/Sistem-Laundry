<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$periode = $_GET['periode'] ?? 'harian';
$start   = $_GET['start'] ?? date('Y-m-01');
$end     = $_GET['end'] ?? date('Y-m-t');

// Generate Laporan (CREATE)
if (isset($_GET['generate'])) {
    // Bisa ditambahkan logic simpan snapshot laporan jika diperlukan
    $message = "✅ Laporan periode $periode berhasil digenerate!";
}

// Query utama dengan GROUP BY sesuai periode
$group_by = "DATE(L.Tanggal_Masuk)";
if ($periode == 'mingguan') {
    $group_by = "YEARWEEK(L.Tanggal_Masuk)";
} elseif ($periode == 'bulanan') {
    $group_by = "DATE_FORMAT(L.Tanggal_Masuk, '%Y-%m')";
}

$sql = "
    SELECT 
        DATE(L.Tanggal_Masuk) as tanggal,
        COUNT(*) as jumlah_order,
        SUM(P.Total_Bayar) as omset,
        SUM(P.Total_Bayar) as bersih
    FROM Laundry L 
    JOIN Pembayaran P ON L.Id_Laundry = P.Id_Laundry
    WHERE L.Tanggal_Masuk BETWEEN '$start' AND '$end'
    GROUP BY $group_by
    ORDER BY tanggal DESC
";

$query = mysqli_query($conn, $sql);
if (!$query) {
    $error_msg = "Query gagal: " . mysqli_error($conn);
}

// Total Keseluruhan
$total_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_order,
        SUM(P.Total_Bayar) as total_omset
    FROM Laundry L 
    JOIN Pembayaran P ON L.Id_Laundry = P.Id_Laundry
    WHERE L.Tanggal_Masuk BETWEEN '$start' AND '$end'
");
$total = mysqli_fetch_assoc($total_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Periodik - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; color: #1E293B; margin-bottom: 40px; }
        .brand i { color: #0066FF; font-size: 28px; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; margin-bottom: 4px; }
        .menu-item.active a { background: #0066FF; color: white; }
        .main-content { flex: 1; padding: 40px; }
        .card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        th { background: #F8FAFC; font-weight: 600; color: #374151; }
        .btn { padding: 10px 20px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: #0066FF; color: white; }
        .btn-danger { background: #EF4444; color: white; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-soap"></i>
                <span>ILHAM LAUNDRY</span>
            </div>
            <ul class="menu-list" style="list-style:none;">
                <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
                <li class="menu-item"><a href="manajemen_jenis_laundry.php"><i class="fa-solid fa-tags"></i> Jenis Laundry</a></li>
                <li class="menu-item"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
                <li class="menu-item active"><a href="laporan_periodik.php"><i class="fa-solid fa-file-chart-column"></i> Laporan Periodik</a></li>
                <li class="menu-item"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
            </ul>
        </div>
        <a href="logout.php" style="background:#FEE2E2; color:#EF4444; text-align:center; text-decoration:none; padding:12px; border-radius:12px; margin-top:auto;">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2 style="margin-bottom: 24px; color: #1E293B;">Laporan Periodik</h2>

        <?php if($message): ?>
            <div class="card" style="background:#DCFCE7; color:#166534;"><?= $message ?></div>
        <?php endif; ?>
        <?php if(isset($error_msg)): ?>
            <div class="card" style="color:red;"><?= $error_msg ?></div>
        <?php endif; ?>

        <!-- Filter & Generate Laporan -->
        <div class="card">
            <form method="GET">
                <select name="periode" style="padding:12px; border-radius:12px; border:1px solid #E5E7EB; margin-right:10px;">
                    <option value="harian" <?= $periode=='harian'?'selected':'' ?>>Harian</option>
                    <option value="mingguan" <?= $periode=='mingguan'?'selected':'' ?>>Mingguan</option>
                    <option value="bulanan" <?= $periode=='bulanan'?'selected':'' ?>>Bulanan</option>
                </select>
                <input type="date" name="start" value="<?= htmlspecialchars($start) ?>" style="padding:12px; border-radius:12px; border:1px solid #E5E7EB;">
                s/d
                <input type="date" name="end" value="<?= htmlspecialchars($end) ?>" style="padding:12px; border-radius:12px; border:1px solid #E5E7EB; margin-right:10px;">
                <button type="submit" name="generate" class="btn btn-primary">Generate Laporan</button>
            </form>
        </div>

        <!-- Ringkasan -->
        <div class="card">
            <h3>Ringkasan Periode</h3>
            <p><strong>Total Order:</strong> <?= number_format($total['total_order'] ?? 0) ?> | 
               <strong>Total Omset:</strong> Rp <?= number_format($total['total_omset'] ?? 0) ?></p>
        </div>

        <!-- Tabel Detail -->
        <div class="card">
            <h3>Detail Laporan (<?= ucfirst($periode) ?>)</h3>
            <table>
                <tr>
                    <th>Tanggal / Periode</th>
                    <th>Jumlah Order</th>
                    <th>Omset</th>
                    <th>Keuntungan Bersih</th>
                    <th>Aksi</th>
                </tr>
                <?php if (isset($query) && mysqli_num_rows($query) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= $row['tanggal'] ?></td>
                        <td><?= number_format($row['jumlah_order']) ?></td>
                        <td>Rp <?= number_format($row['omset'] ?? 0) ?></td>
                        <td>Rp <?= number_format($row['bersih'] ?? 0) ?></td>
                        <td>
                            <button class="btn btn-danger" onclick="if(confirm('Hapus data periode ini?')) window.location='?hapus=<?= $row['tanggal'] ?>'">Hapus</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:60px; color:#666;">
                            Tidak ada data transaksi pada periode tersebut.
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>