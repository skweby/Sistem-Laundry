<?php
session_start();
require_once '../../config/database.php'; // Sesuaikan lokasi database.php Anda

// Proteksi: Jika belum login, paksa ke login.php
if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

$id_pelanggan = $_SESSION['id_pelanggan'];
$nama_pelanggan = $_SESSION['nama_pelanggan'];

// PERBAIKAN: Nama tabel diubah ke 'laundry', kolom ke 'Id_Pelanggan' dan 'Tanggal_Masuk'
$query_riwayat = mysqli_query($conn, "SELECT * FROM laundry WHERE Id_Pelanggan = '$id_pelanggan' ORDER BY Tanggal_Masuk DESC");

// ERROR HANDLING: Mencegah Fatal Error jika query gagal
if (!$query_riwayat) {
    die("<div style='padding:20px; background:#FEE2E2; color:#991B1B; font-family:sans-serif; border-radius:8px; margin:20px;'>
            <strong>Gagal Memuat Riwayat Transaksi!</strong><br>
            Pesan Kesalahan MySQL: <code style='background:#FFF; padding:2px 6px; border:1px solid #FCA5A5;'>" . mysqli_error($conn) . "</code>
         </div>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - ILHAM Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F4F7FE; display: flex; justify-content: center; }
        .desktop-wrapper { width: 100%; max-width: 480px; min-height: 100vh; background: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.05); position: relative; }
        .app-header-mini { background: linear-gradient(135deg, #0066FF, #0052CC); color: white; padding: 24px; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .back-btn { color: white; text-decoration: none; font-size: 14px; font-weight: 600; display: flex; gap: 6px; align-items: center; }
        .order-container { padding: 20px; }
        .order-card { background: #F8FAFC; border: 1px solid #E2E8F0; padding: 16px; border-radius: 16px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 8px; }
        .order-card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #CBD5E1; padding-bottom: 8px; margin-bottom: 4px; }
        .invoice-num { font-weight: 700; color: #1A1A1A; font-size: 14px; }
        
        /* Pewarnaan Status Badge mengikuti Manajemen Order Admin */
        .status-badge-order { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-proses { background: #FEF3C7; color: #D97706; } /* Kuning Sedang Diproses */
        .status-selesai { background: #DCFCE7; color: #166534; } /* Hijau Selesai */
        .status-baru { background: #E0F2FE; color: #0369A1; }    /* Biru Baru Masuk */

        .order-details { font-size: 13px; color: #4A4A4A; display: flex; flex-direction: column; gap: 5px; }
        .total-pay { align-self: flex-end; font-weight: 800; color: #0066FF; font-size: 15px; margin-top: 5px; }
        .empty-state { text-align: center; padding: 60px 20px; color: #718096; }
        .empty-state i { font-size: 48px; color: #CBD5E1; margin-bottom: 12px; }
    </style>
</head>
<body>

<div class="desktop-wrapper">
    <header class="app-header-mini">
        <div class="header-top">
            <a href="../../index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Beranda Utama</a>
            <span style="font-size: 11px; opacity: 0.9; font-weight: 600; background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 8px;">Pelanggan: <?php echo htmlspecialchars($nama_pelanggan); ?></span>
        </div>
        <h4 style="font-weight: 800; font-size: 18px;">Dashboard Riwayat Order</h4>
    </header>

    <main class="order-container">
        <?php if (mysqli_num_rows($query_riwayat) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($query_riwayat)): 
                // PERBAIKAN: Menggunakan key 'Status' dengan S besar sesuai database
                $status_class = 'status-baru';
                $status_text = isset($order['Status']) ? strtolower($order['Status']) : '';
                
                if ($status_text == 'proses' || $status_text == 'sedang diproses') {
                    $status_class = 'status-proses';
                } elseif ($status_text == 'selesai' || $status_text == 'diambil' || $status_text == 'sudah diambil') {
                    $status_class = 'status-selesai';
                }
            ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <span class="invoice-num">#TRX-<?php echo $order['Id_Laundry']; ?></span>
                        <span class="status-badge-order <?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($order['Status']); ?>
                        </span>
                    </div>
                    <div class="order-details">
                        <p><i class="fa-regular fa-calendar-days" style="color:#0066FF;"></i> <strong>Tanggal Masuk:</strong> <?php echo date('d M Y', strtotime($order['Tanggal_Masuk'])); ?></p>
                        <p><i class="fa-regular fa-calendar-check" style="color: #10B981;"></i> <strong>Estimasi Keluar:</strong> <?php echo !empty($order['Tanggal_Keluar']) ? date('d M Y', strtotime($order['Tanggal_Keluar'])) : '-'; ?></p>
                    </div>
                    <div class="total-pay">
                        Rp <?php echo number_format($order['Total'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <h5 style="font-size: 15px; font-weight: 700; color:#1A1A1A;">Belum ada riwayat order</h5>
                <p style="font-size: 12px; margin-top: 4px;">Silakan buat pesanan melalui menu laundry pada halaman utama.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>