<?php
session_start();
require_once '../config/database.php';

// Proteksi untuk karyawan
if (!isset($_SESSION['karyawan_logged'])) { 
    header("Location: login.php"); 
    exit(); 
}

$notif = '';

// =========================================================
// PROSES: PENGATURAN TOKO (HANYA STATUS & JAM)
// =========================================================
if (isset($_POST['save_settings'])) {
    $status_toko = mysqli_real_escape_string($conn, $_POST['status_toko']);
    $jam_buka    = mysqli_real_escape_string($conn, $_POST['jam_buka']);
    $_SESSION['status_toko']   = $status_toko;
    $_SESSION['jam_operasional'] = $jam_buka;
    $notif = "Pengaturan toko berhasil disimpan!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Toko - Karyawan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }

        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 32px; color: #1E293B; }
        .brand i { color: #8B5CF6; font-size: 22px; }
        .menu-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .menu-item.active a { background: #8B5CF6; color: white; }
        .menu-item a:hover { background: #F1F5F9; color: #1E293B; }
        .btn-logout { background: #FFE4E6; color: #E11D48; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: block; }

        .main-content { flex: 1; padding: 40px; }
        .page-title { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 6px; }
        .page-sub   { font-size: 13px; color: #64748B; margin-bottom: 24px; }

        .notif-box { padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .notif-ok  { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }

        .card { background: white; padding: 28px; border-radius: 16px; border: 1px solid #E2E8F0; max-width: 600px; }
        .card-title { font-size: 15px; font-weight: 800; color: #1E293B; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .card-title i { color: #8B5CF6; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 7px; }
        .input-box { width: 100%; padding: 11px 14px; border: 1px solid #E2E8F0; border-radius: 10px; font-size: 14px; color: #1E293B; outline: none; transition: 0.2s; }
        .input-box:focus { border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.08); }
        select.input-box { cursor: pointer; }
        .input-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        .btn-primary { padding: 11px 20px; background: #8B5CF6; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { background: #7C3AED; }
        .btn-green { background: #10B981; }
        .btn-green:hover { background: #059669; }

        .badge-karyawan { background: #EDE9FE; color: #7C3AED; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; margin-left: 8px; }
        .info-box { background: #F8FAFC; padding: 16px; border-radius: 12px; border: 1px solid #E2E8F0; margin-top: 20px; }
        .info-box p { font-size: 13px; color: #64748B; }
        .info-box i { color: #8B5CF6; margin-right: 8px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand"><i class="fa-solid fa-soap"></i><span>ILHAM LAUNDRY</span></div>
        <ul class="menu-list">
            <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
            <li class="menu-item active"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
        </ul>
    </div>
    <a href="logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main-content">
    <p class="page-title">
        <i class="fa-solid fa-gear"></i> Pengaturan Toko 
        <span class="badge-karyawan"><i class="fa-solid fa-user-tie"></i> Karyawan</span>
    </p>
    <p class="page-sub">Kelola status toko dan jam operasional.</p>

    <?php if ($notif): ?>
        <div class="notif-box notif-ok">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo $notif; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title"><i class="fa-solid fa-store"></i> Konfigurasi Toko</div>
        
        <div class="info-box">
            <p><i class="fa-solid fa-info-circle"></i> Anda hanya dapat mengatur status toko dan jam operasional. Untuk pengaturan lengkap (jenis servis, harga, dll) silakan hubungi admin.</p>
        </div>

        <form action="" method="POST" style="margin-top: 20px;">
            <div class="input-row">
                <div class="form-group">
                    <label>Status Toko</label>
                    <select class="input-box" name="status_toko">
                        <?php
                        $st_now = $_SESSION['status_toko'] ?? 'BUKA';
                        $opts = ['BUKA' => 'Buka / Open', 'TUTUP' => 'Tutup / Closed'];
                        foreach ($opts as $val => $lbl) {
                            $sel = ($st_now === $val) ? 'selected' : '';
                            echo "<option value='$val' $sel>$lbl</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" class="input-box" name="jam_buka" value="<?php echo $_SESSION['jam_operasional'] ?? '08:00 - 20:00 WIB'; ?>" placeholder="08:00 - 20:00 WIB">
                </div>
            </div>
            <button type="submit" name="save_settings" class="btn-primary btn-green">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
            </button>
        </form>
    </div>
</div>

</body>
</html>