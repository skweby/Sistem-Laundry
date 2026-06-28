<?php
session_start();
require_once '../../config/database.php';

// =========================================================
// CEK PARAMETER top UNTUK REDIRECT KE #top (tanpa JavaScript)
// =========================================================
if (isset($_GET['top']) && $_GET['top'] == '1') {
    $query = $_GET;
    unset($query['top']);
    $new_query = http_build_query($query);
    $url = 'rating.php' . ($new_query ? '?' . $new_query : '') . '#top';
    header("Location: $url");
    exit();
}

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

$id_pelanggan   = (int)$_SESSION['id_pelanggan'];
$nama_pelanggan = $_SESSION['nama_pelanggan'];

// =========================================================
// AMBIL ORDER MILIK PELANGGAN YANG SUDAH SELESAI (BISA DIBERI RATING)
// =========================================================
$query_order = mysqli_query($conn, "
    SELECT l.Id_Laundry, l.Tanggal_Masuk, l.Total, 
           r.Id_Rating, r.Jumlah_Bintang, r.Komentar
    FROM laundry l
    LEFT JOIN rating r ON r.Id_Laundry = l.Id_Laundry AND r.Id_Pelanggan = l.Id_Pelanggan
    WHERE l.Id_Pelanggan = $id_pelanggan AND l.Status = 'Selesai'
    ORDER BY l.Id_Laundry DESC
");

// =========================================================
// AMBIL RATING DARI PELANGGAN LAIN (PUBLIK)
// =========================================================
$query_lain = mysqli_query($conn, "
    SELECT r.Jumlah_Bintang, r.Tanggal_Rating, r.Id_Laundry, p.Nama, r.Komentar
    FROM rating r
    JOIN pelanggan p ON r.Id_Pelanggan = p.IdPelanggan
    WHERE r.Id_Pelanggan != $id_pelanggan
    ORDER BY r.Tanggal_Rating DESC
    LIMIT 50
");

$q_avg = mysqli_query($conn, "SELECT AVG(Jumlah_Bintang) as avg_rating, COUNT(*) as total FROM rating");
$r_avg = mysqli_fetch_assoc($q_avg);
$avg_rating = $r_avg['avg_rating'] ? round($r_avg['avg_rating'], 1) : 0;
$total_rating = $r_avg['total'] ?? 0;

$notif = isset($_GET['notif']) ? $_GET['notif'] : '';

function tampilkanBintang($jumlah, $size = '14px') {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $color = $i <= $jumlah ? '#F59E0B' : '#E2E8F0';
        $html .= "<i class='fa-solid fa-star' style='color:$color; font-size:$size;'></i>";
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating - ILHAM Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F4F7FE; display: flex; justify-content: center; padding-bottom: 80px; }
        .desktop-wrapper { width: 100%; max-width: 480px; min-height: 100vh; background: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.05); position: relative; padding-bottom: 80px; }
        .app-header-mini { background: linear-gradient(135deg, #0066FF, #0052CC); color: white; padding: 24px; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .back-btn { color: white; text-decoration: none; font-size: 14px; font-weight: 600; display: flex; gap: 6px; align-items: center; }
        .avg-row { display:flex; align-items:center; gap:10px; margin-top:6px; }
        .avg-num { font-size: 26px; font-weight: 800; }
        .avg-sub { font-size: 11px; opacity: 0.85; }

        .tabs { display: flex; padding: 16px 20px 0; gap: 8px; }
        .tab-btn { flex: 1; padding: 10px; text-align: center; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; background: #F1F5F9; color: #64748B; border: none; }
        .tab-btn.active { background: #0066FF; color: white; }

        .alert { margin: 16px 20px 0; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

        .order-container { padding: 20px; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .rating-card { background: #F8FAFC; border: 1px solid #E2E8F0; padding: 16px; border-radius: 16px; margin-bottom: 16px; }
        .rc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .rc-invoice { font-weight: 700; color: #1A1A1A; font-size: 14px; }
        .rc-date { font-size: 11px; color: #94A3B8; }
        .rc-total { font-size: 12px; color: #0066FF; font-weight: 700; margin-bottom: 10px; }

        .star-input { display: flex; flex-direction: row-reverse; gap: 4px; justify-content: flex-start; margin-bottom: 10px; }
        .star-input input { display: none; }
        .star-input label { font-size: 26px; color: #CBD5E1; cursor: pointer; transition: 0.15s; }
        .star-input input:checked ~ label,
        .star-input label:hover,
        .star-input label:hover ~ label { color: #F59E0B; }

        .komentar-box { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #E2E8F0; 
            border-radius: 10px; 
            font-size: 13px; 
            font-family: inherit; 
            resize: vertical; 
            min-height: 60px; 
            margin-bottom: 10px;
            background: white;
        }
        .komentar-box:focus { border-color: #0066FF; outline: none; }

        .rc-actions { display: flex; gap: 8px; }
        .btn-kirim { flex: 1; padding: 10px; background: #0066FF; color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; }
        .btn-hapus { padding: 10px 14px; background: #FEE2E2; color: #DC2626; border: none; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: none; display:flex; align-items:center; }
        .rated-badge { font-size: 11px; background: #DCFCE7; color: #166534; padding: 3px 10px; border-radius: 20px; font-weight: 700; }

        .other-card { background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 16px; margin-bottom: 12px; }
        .other-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .other-name { font-weight: 700; font-size: 13px; color: #1E293B; }
        .other-date { font-size: 10px; color: #94A3B8; }
        .other-comment { font-size: 12.5px; color: #475569; margin-top: 6px; line-height: 1.5; background: #F1F5F9; padding: 8px 12px; border-radius: 8px; }
        .other-order { font-size: 10px; color: #94A3B8; margin-top: 6px; }

        .empty-state { text-align: center; padding: 60px 20px; color: #718096; }
        .empty-state i { font-size: 48px; color: #CBD5E1; margin-bottom: 12px; }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            max-width: 480px;
            height: 65px;
            background: #ffffff;
            border-top: 1px solid #E2E8F0;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 999;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.03);
        }
        .nav-item {
            text-decoration: none;
            color: #94A3B8;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            flex: 1;
            text-align: center;
        }
        .nav-item i { font-size: 18px; }
        .nav-item.active { color: #0066FF; }
    </style>
</head>
<body>
<!-- ELEMEN TARGET UNTUK SCROLL KE ATAS -->
<div id="top"></div>

<div class="desktop-wrapper">
    <header class="app-header-mini">
        <div class="header-top">
            <a href="riwayat.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Beranda Utama</a>
            <span style="font-size: 11px; opacity: 0.9; font-weight: 600; background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 8px;">Pelanggan: <?php echo htmlspecialchars($nama_pelanggan); ?></span>
        </div>
        <h4 style="font-weight: 800; font-size: 18px;">Rating &amp; Ulasan</h4>
        <div class="avg-row">
            <span class="avg-num"><?php echo $avg_rating; ?></span>
            <div>
                <div><?php echo tampilkanBintang(round($avg_rating)); ?></div>
                <div class="avg-sub"><?php echo $total_rating; ?> ulasan dari pelanggan</div>
            </div>
        </div>
    </header>

    <?php if ($notif === 'tambah_ok'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Rating berhasil dikirim. Terima kasih atas ulasan Anda!</div>
    <?php elseif ($notif === 'update_ok'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Rating berhasil diperbarui.</div>
    <?php elseif ($notif === 'hapus_ok'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Rating berhasil dihapus.</div>
    <?php elseif ($notif === 'rating_invalid'): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> Pilih bintang 1-5 sebelum mengirim rating.</div>
    <?php elseif ($notif === 'order_invalid'): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> Order tidak ditemukan atau belum selesai, tidak bisa diberi rating.</div>
    <?php endif; ?>

    <div class="tabs">
        <button type="button" class="tab-btn active" id="btn-tab-saya" onclick="gantiTab('saya')">Pesanan Saya</button>
        <button type="button" class="tab-btn" id="btn-tab-lain" onclick="gantiTab('lain')">Rating Pelanggan Lain</button>
    </div>

    <main class="order-container">

        <!-- TAB PESANAN SAYA -->
        <div class="tab-panel active" id="panel-saya">
            <?php if (mysqli_num_rows($query_order) > 0): ?>
                <?php while ($order = mysqli_fetch_assoc($query_order)):
                    $sudah_rating = !empty($order['Id_Rating']);
                    $rating_val = $sudah_rating ? (int)$order['Jumlah_Bintang'] : 0;
                    $komentar = $sudah_rating ? htmlspecialchars($order['Komentar']) : '';
                ?>
                    <div class="rating-card" id="order-<?php echo $order['Id_Laundry']; ?>">
                        <div class="rc-header">
                            <span class="rc-invoice">#TRX-<?php echo $order['Id_Laundry']; ?></span>
                            <?php if ($sudah_rating): ?>
                                <span class="rated-badge"><i class="fa-solid fa-check"></i> Sudah dirating</span>
                            <?php endif; ?>
                        </div>
                        <div class="rc-date">Tanggal Masuk: <?php echo date('d M Y', strtotime($order['Tanggal_Masuk'])); ?></div>
                        <div class="rc-total">Total: Rp <?php echo number_format($order['Total'], 0, ',', '.'); ?></div>

                        <form action="process_rating.php" method="POST">
                            <input type="hidden" name="id_laundry" value="<?php echo $order['Id_Laundry']; ?>">

                            <div class="star-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?php echo $i; ?>-<?php echo $order['Id_Laundry']; ?>" value="<?php echo $i; ?>" <?php echo ($rating_val == $i) ? 'checked' : ''; ?> required>
                                    <label for="star<?php echo $i; ?>-<?php echo $order['Id_Laundry']; ?>"><i class="fa-solid fa-star"></i></label>
                                <?php endfor; ?>
                            </div>

                            <!-- TEXTAREA KOMENTAR -->
                            <textarea name="komentar" class="komentar-box" placeholder="Tulis komentar Anda (opsional)"><?php echo $komentar; ?></textarea>

                            <div class="rc-actions">
                                <button type="submit" class="btn-kirim">
                                    <i class="fa-solid fa-paper-plane"></i> <?php echo $sudah_rating ? 'Update Rating' : 'Kirim Rating'; ?>
                                </button>
                                <?php if ($sudah_rating): ?>
                                    <a href="process_rating.php?delete=1&id_laundry=<?php echo $order['Id_Laundry']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus rating ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-star-half-stroke"></i>
                    <h5 style="font-size: 15px; font-weight: 700; color:#1A1A1A;">Belum ada pesanan yang selesai</h5>
                    <p style="font-size: 12px; margin-top: 4px;">Rating bisa diberikan setelah pesanan laundry Anda berstatus "Selesai".</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB RATING PELANGGAN LAIN -->
        <div class="tab-panel" id="panel-lain">
            <?php if (mysqli_num_rows($query_lain) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($query_lain)): ?>
                    <div class="other-card">
                        <div class="other-head">
                            <span class="other-name"><i class="fa-solid fa-circle-user" style="color:#94A3B8;"></i> <?php echo htmlspecialchars($r['Nama']); ?></span>
                            <span class="other-date"><?php echo date('d M Y', strtotime($r['Tanggal_Rating'])); ?></span>
                        </div>
                        <div><?php echo tampilkanBintang($r['Jumlah_Bintang']); ?></div>
                        <?php if (!empty($r['Komentar'])): ?>
                            <div class="other-comment"><?php echo htmlspecialchars($r['Komentar']); ?></div>
                        <?php endif; ?>
                        <div class="other-order">Order #TRX-<?php echo $r['Id_Laundry']; ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-comment-slash"></i>
                    <h5 style="font-size: 15px; font-weight: 700; color:#1A1A1A;">Belum ada ulasan</h5>
                    <p style="font-size: 12px; margin-top: 4px;">Belum ada pelanggan lain yang memberi rating.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
function gantiTab(tab) {
    document.getElementById('panel-saya').classList.toggle('active', tab === 'saya');
    document.getElementById('panel-lain').classList.toggle('active', tab === 'lain');
    document.getElementById('btn-tab-saya').classList.toggle('active', tab === 'saya');
    document.getElementById('btn-tab-lain').classList.toggle('active', tab === 'lain');
}
</script>

</body>
</html>