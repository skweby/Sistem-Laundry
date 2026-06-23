<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

$id_pelanggan   = (int)$_SESSION['id_pelanggan'];
$nama_pelanggan = $_SESSION['nama_pelanggan'];

// Ambil semua order milik pelanggan (terbaru di atas)
$query_orders = mysqli_query($conn, "
    SELECT Id_Laundry, Tanggal_Masuk, Status 
    FROM Laundry 
    WHERE Id_Pelanggan = $id_pelanggan 
    ORDER BY Id_Laundry DESC
");

if (!$query_orders) {
    die("<div style='padding:20px; background:#FEE2E2; color:#991B1B; font-family:sans-serif; border-radius:8px; margin:20px;'>
            <strong>Gagal Memuat Chat!</strong><br>
            Pesan Kesalahan MySQL: <code style='background:#FFF; padding:2px 6px; border:1px solid #FCA5A5;'>" . mysqli_error($conn) . "</code>
         </div>");
}

$daftar_order = [];
while ($o = mysqli_fetch_assoc($query_orders)) {
    $daftar_order[] = $o;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Notifikasi - ILHAM Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F4F7FE; display: flex; justify-content: center; }
        .desktop-wrapper { width: 100%; max-width: 480px; min-height: 100vh; background: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.05); position: relative; }
        .app-header-mini { background: linear-gradient(135deg, #0066FF, #0052CC); color: white; padding: 24px; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .back-btn { color: white; text-decoration: none; font-size: 14px; font-weight: 600; display: flex; gap: 6px; align-items: center; }

        .chat-container { padding: 20px; }
        .order-thread { margin-bottom: 22px; }
        .thread-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .thread-invoice { font-weight: 700; color: #1A1A1A; font-size: 13px; }
        .thread-status { padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; background: #E0F2FE; color: #0369A1; }
        .thread-status.selesai { background: #DCFCE7; color: #166534; }

        .bubble-row { display: flex; gap: 8px; margin-bottom: 12px; align-items: flex-start; }
        .bubble-avatar { width: 32px; height: 32px; border-radius: 50%; background: #0066FF; color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
        .bubble-content { max-width: 78%; }
        .bubble-sender { font-size: 10.5px; color: #94A3B8; font-weight: 700; margin-bottom: 3px; }
        .bubble-text { background: #F1F5F9; padding: 10px 14px; border-radius: 14px; border-top-left-radius: 4px; font-size: 13px; color: #1E293B; line-height: 1.5; }
        .bubble-time { font-size: 10px; color: #CBD5E1; margin-top: 4px; }

        .empty-thread { font-size: 12px; color: #94A3B8; padding: 10px 0 0 40px; font-style: italic; }
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
        <h4 style="font-weight: 800; font-size: 18px;">Chat &amp; Notifikasi Status</h4>
        <p style="font-size: 11px; opacity: 0.85; margin-top: 4px;">Update otomatis dari sistem setiap status pesanan Anda berubah.</p>
    </header>

    <main class="chat-container">
        <?php if (count($daftar_order) > 0): ?>
            <?php foreach ($daftar_order as $order):
                $id_laundry = $order['Id_Laundry'];
                $status_text = strtolower($order['Status']);
                $is_selesai = in_array($status_text, ['selesai']);

                $query_chat = mysqli_query($conn, "
                    SELECT pesan, status, created_at 
                    FROM chat_status 
                    WHERE id_laundry = $id_laundry AND id_pelanggan = $id_pelanggan 
                    ORDER BY created_at ASC, id_chat ASC
                ");
            ?>
                <div class="order-thread">
                    <div class="thread-head">
                        <span class="thread-invoice">#TRX-<?php echo $id_laundry; ?> &middot; <?php echo date('d M Y', strtotime($order['Tanggal_Masuk'])); ?></span>
                        <span class="thread-status <?php echo $is_selesai ? 'selesai' : ''; ?>"><?php echo htmlspecialchars($order['Status']); ?></span>
                    </div>

                    <?php if ($query_chat && mysqli_num_rows($query_chat) > 0): ?>
                        <?php while ($chat = mysqli_fetch_assoc($query_chat)): ?>
                            <div class="bubble-row">
                                <div class="bubble-avatar"><i class="fa-solid fa-soap"></i></div>
                                <div class="bubble-content">
                                    <div class="bubble-sender">Sistem ILHAM Laundry</div>
                                    <div class="bubble-text"><?php echo nl2br(htmlspecialchars($chat['pesan'])); ?></div>
                                    <div class="bubble-time"><?php echo date('d M Y, H:i', strtotime($chat['created_at'])); ?> WIB</div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-thread">Belum ada update status untuk pesanan ini.</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-comments"></i>
                <h5 style="font-size: 15px; font-weight: 700; color:#1A1A1A;">Belum ada pesanan</h5>
                <p style="font-size: 12px; margin-top: 4px;">Chat notifikasi akan muncul di sini setiap status pesanan Anda berubah.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include '../../views/partials/bottom_nav.php'; ?>

</body>
</html>
