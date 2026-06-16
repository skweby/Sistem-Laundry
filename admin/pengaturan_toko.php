<?php
session_start();
if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }
$sukses = '';

if (isset($_POST['save_settings'])) {
    $sukses = "Pengaturan Toko Berhasil Diperbarui!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Toko - Admin</title>
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
        .form-container { background: white; padding: 32px; border-radius: 16px; border: 1px solid #E2E8F0; max-width: 500px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .input-box { width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; }
        .btn-save { padding: 10px 20px; background: #22C55E; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand"><i class="fa-solid fa-soap"></i><span>ILHAM LAUNDRY</span></div>
        <ul class="menu-list">
            <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
            <li class="menu-item"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
            <li class="menu-item"><a href="laporan_transaksi.php"><i class="fa-solid fa-coins"></i> Laporan Transaksi</a></li>
            <li class="menu-item"><a href="manajemen_stok.php"><i class="fa-solid fa-boxes-stacked"></i> Manajemen Stok</a></li>
            <li class="menu-item active"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h2><i class="fa-solid fa-gear"></i> Pengaturan Konfigurasi Toko</h2>
            <?php if($sukses): ?><p style="color: green; font-weight: 600; margin: 10px 0;"><?php echo $sukses; ?></p><?php endif; ?>
            <form action="" method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Status Toko</label>
                    <select class="input-box" name="status_toko">
                        <option>Buka / Open</option>
                        <option>Tutup / Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" class="input-box" name="jam_buka" value="08:00 - 20:00 WIB">
                </div>
                <button type="submit" name="save_settings" class="btn-save">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</body>
</html>