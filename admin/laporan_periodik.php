<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$periode = $_GET['periode'] ?? 'harian';
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Periodik</title>
    <style> /* Reuse style dari dashboard */ </style>
</head>
<body>
<div class="container">
    <h2>Laporan Periodik</h2>
    
    <form method="GET">
        <select name="periode">
            <option value="harian">Harian</option>
            <option value="bulanan">Bulanan</option>
            <option value="tahunan">Tahunan</option>
        </select>
        <input type="date" name="start" value="<?= $start ?>">
        <input type="date" name="end" value="<?= $end ?>">
        <button type="submit">Filter</button>
    </form>

    <h3>Omset & Jumlah Order</h3>
    <?php
    $query = mysqli_query($conn, "
        SELECT 
            DATE(Tanggal_Masuk) as tanggal,
            COUNT(*) as jumlah_order,
            SUM(Total_Bayar) as omset
        FROM Laundry L 
        JOIN Pembayaran P ON L.Id_Laundry = P.Id_Laundry
        WHERE Tanggal_Masuk BETWEEN '$start' AND '$end'
        GROUP BY DATE(Tanggal_Masuk)
        ORDER BY tanggal DESC
    ");

    echo "<table><tr><th>Tanggal</th><th>Jumlah Order</th><th>Omset</th></tr>";
    while ($row = mysqli_fetch_assoc($query)) {
        echo "<tr>
            <td>{$row['tanggal']}</td>
            <td>{$row['jumlah_order']}</td>
            <td>Rp " . number_format($row['omset']) . "</td>
        </tr>";
    }
    echo "</table>";
    ?>
</div>
</body>
</html>