<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

$id_pelanggan = $_SESSION['id_pelanggan'];
$nama_pelanggan = $_SESSION['nama_pelanggan'];

// Ambil semua order pelanggan (URUTAN TERBARU DI ATAS)
$query_riwayat = mysqli_query($conn, "SELECT * FROM laundry WHERE Id_Pelanggan = '$id_pelanggan' ORDER BY Id_Laundry DESC");

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
    <title>Riwayat Transaksi - RIFFANASH Laundry</title>
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
        .status-badge-order { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-proses { background: #FEF3C7; color: #D97706; }
        .status-selesai { background: #DCFCE7; color: #166534; }
        .status-baru { background: #E0F2FE; color: #0369A1; }
        .status-selesai-proses { background: #DBEAFE; color: #1D4ED8; }
        .order-details { font-size: 13px; color: #4A4A4A; display: flex; flex-direction: column; gap: 5px; }
        .order-details .detail-item { display: flex; justify-content: space-between; padding: 2px 0; border-bottom: 1px dashed #E2E8F0; }
        .order-details .detail-item:last-child { border-bottom: none; }
        .order-details .detail-item .nama-layanan { color: #1E293B; flex: 1; }
        .order-details .detail-item .harga-layanan { color: #0066FF; font-weight: 600; }
        .total-pay { align-self: flex-end; font-weight: 800; color: #0066FF; font-size: 15px; margin-top: 5px; }
        .empty-state { text-align: center; padding: 60px 20px; color: #718096; }
        .empty-state i { font-size: 48px; color: #CBD5E1; margin-bottom: 12px; }
        .estimasi-terlambat { color: #EF4444; font-weight: 600; font-size: 11px; }
        .catatan-order { font-size: 12px; color: #64748B; margin-top: 4px; font-style: italic; }
        .detail-header { font-weight: 600; color: #1E293B; margin-top: 2px; margin-bottom: 4px; font-size: 12px; display: flex; align-items: center; gap: 6px; }
        .detail-header i { color: #0066FF; }
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
                $status_class = 'status-baru';
                $status_text = isset($order['Status']) ? strtolower($order['Status']) : '';
                if (in_array($status_text, ['proses', 'sedang diproses', 'pickup'])) {
                    $status_class = 'status-proses';
                } elseif (in_array($status_text, ['selesai', 'diambil', 'sudah diambil', 'diantar'])) {
                    $status_class = 'status-selesai';
                } elseif ($status_text == 'selesai proses') {
                    $status_class = 'status-selesai-proses';
                }

                $estimasi = !empty($order['Tanggal_Keluar']) ? strtotime($order['Tanggal_Keluar']) : 0;
                $hari_ini = time();
                $is_terlambat = ($estimasi && $estimasi < $hari_ini && !in_array($status_text, ['selesai', 'diambil', 'sudah diambil', 'diantar']));

                // Ambil detail layanan untuk order ini
                $id_laundry = $order['Id_Laundry'];
                $query_detail = mysqli_query($conn, "
                    SELECT d.jumlah, d.subtotal, j.namaJenis, t.namaTipe 
                    FROM detail_laundry d
                    JOIN jenis_laundry j ON d.idJenis = j.idJenis
                    LEFT JOIN tipe_laundry t ON d.idTipe = t.idTipe
                    WHERE d.Id_Laundry = '$id_laundry'
                ");
                $detail_items = [];
                while ($detail = mysqli_fetch_assoc($query_detail)) {
                    $nama_layanan = $detail['namaJenis'];
                    if ($detail['namaTipe'] && $detail['namaTipe'] != 'Regular') {
                        $nama_layanan .= ' (' . $detail['namaTipe'] . ')';
                    }
                    $detail_items[] = [
                        'nama' => $nama_layanan,
                        'jumlah' => $detail['jumlah'],
                        'subtotal' => $detail['subtotal']
                    ];
                }

                // Ambil berat dari order
                $berat = isset($order['Berat_Kg']) ? (float)$order['Berat_Kg'] : 0;
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
                        <p>
                            <i class="fa-regular fa-calendar-check" style="color: #10B981;"></i> 
                            <strong>Estimasi Keluar:</strong> 
                            <?php echo !empty($order['Tanggal_Keluar']) ? date('d M Y', strtotime($order['Tanggal_Keluar'])) : '-'; ?>
                            <?php if ($is_terlambat): ?>
                                <span class="estimasi-terlambat">⚠️ Melewati estimasi</span>
                            <?php endif; ?>
                        </p>

                        <?php if ($berat > 0): ?>
                            <p><i class="fa-solid fa-weight-scale" style="color:#8B5CF6;"></i> <strong>Berat:</strong> 
                                <?php 
                                // Cek apakah berat memiliki desimal
                                if (floor($berat) == $berat) {
                                    echo number_format($berat, 0, ',', '.');
                                } else {
                                    echo number_format($berat, 1, ',', '.');
                                }
                                ?> Kg
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($detail_items)): ?>
                            <div class="detail-header">
                                <i class="fa-solid fa-list-ul"></i> Detail Layanan
                            </div>
                            <?php foreach ($detail_items as $item): ?>
                                <div class="detail-item">
                                    <span class="nama-layanan">
                                        <?php echo htmlspecialchars($item['nama']); ?> 
                                        <?php if ($item['jumlah'] > 1): ?>
                                            <span style="font-size:11px; color:#94A3B8;">(x<?php echo $item['jumlah']; ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="harga-layanan">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($order['Catatan'])): ?>
                            <div class="catatan-order">
                                <i class="fa-regular fa-note-sticky"></i> Catatan: <?php echo htmlspecialchars($order['Catatan']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="total-pay">
                        Total: Rp <?php echo number_format($order['Total'], 0, ',', '.'); ?>
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