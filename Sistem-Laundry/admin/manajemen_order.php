<?php
session_start();
require_once '../config/database.php';

/**
 * Buat pesan WhatsApp berdasarkan status order
 * 
 * @param string $nama      Nama pelanggan
 * @param int $id_order     ID order
 * @param string $status    Status order saat ini
 * @param float $total      Total harga (optional, untuk status tertentu)
 * @return string           Pesan WhatsApp
 */
function buatPesanStatus($nama, $id_order, $status, $total = null) {
    $pesan = "Halo $nama,\n\n";
    
    switch ($status) {
        case 'Baru':
        case 'Diterima':
            $pesan .= "✅ Pesanan laundry Anda dengan ID #$id_order telah kami terima.\n";
            $pesan .= "Kami akan segera memproses pesanan Anda.\n";
            break;
            
        case 'Pickup':
            $pesan .= "🚚 Pesanan laundry Anda dengan ID #$id_order sedang dijemput oleh kurir.\n";
            $pesan .= "Mohon siapkan cucian Anda di lokasi.\n";
            break;
            
        case 'Proses Cuci':
            $pesan .= "🧺 Pesanan laundry Anda dengan ID #$id_order sedang dalam proses pencucian.\n";
            $pesan .= "Kami akan menginformasikan jika sudah selesai.\n";
            break;
            
        case 'Proses Setrika':
            $pesan .= "👕 Pesanan laundry Anda dengan ID #$id_order sedang dalam proses penyetrikaan.\n";
            $pesan .= "Tunggu sebentar lagi ya!\n";
            break;
            
        case 'Selesai Proses':
            $pesan .= "✅ Pesanan laundry Anda dengan ID #$id_order telah SELESAI DIPROSES!\n\n";
            $pesan .= "💰 *Total Biaya: Rp " . number_format($total, 0, ',', '.') . "*\n\n";
            $pesan .= "Silakan lakukan pembayaran dan konfirmasi ke admin untuk pengantaran atau pengambilan.\n";
            $pesan .= "📱 Hubungi kami di wa.me/6283838367497\n";
            break;
            
        case 'Diantar':
            $pesan .= "🚗 Pesanan laundry Anda dengan ID #$id_order sedang dalam perjalanan menuju alamat Anda.\n";
            $pesan .= "Mohon siapkan diri untuk menerima pesanan.\n";
            break;
            
        case 'Selesai':
            $pesan .= "🎉 Pesanan laundry Anda dengan ID #$id_order telah SELESAI!\n\n";
            $pesan .= "Cucian Anda sudah siap untuk diambil/diterima.\n";
            $pesan .= "Terima kasih telah menggunakan layanan ILHAM LAUNDRY 😊\n";
            break;
            
        default:
            $pesan .= "Status laundry Anda dengan ID #$id_order telah diperbarui menjadi:\n";
            $pesan .= "📌 *$status*\n";
            break;
    }
    
    $pesan .= "\n📅 " . date('d-m-Y H:i') . "\n";
    $pesan .= "-----------------------------------\n";
    $pesan .= "ILHAM LAUNDRY\n";
    $pesan .= "📞 wa.me/6283838367497";
    
    return $pesan;
}

if (!isset($_SESSION['user_logged'])) { header("Location: login.php"); exit(); }

// =========================================================
// PROSES UPDATE STATUS ORDER (dan estimasi selesai)
// =========================================================
if (isset($_POST['update_status'])) {
    $id_laundry  = (int)$_POST['id_laundry'];
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_laundry']);
    $tanggal_keluar = !empty($_POST['tanggal_keluar']) ? mysqli_real_escape_string($conn, $_POST['tanggal_keluar']) : NULL;
    
    if ($tanggal_keluar) {
        mysqli_query($conn, "UPDATE Laundry SET Status = '$status_baru', Tanggal_Keluar = '$tanggal_keluar' WHERE Id_Laundry = '$id_laundry'");
    } else {
        mysqli_query($conn, "UPDATE Laundry SET Status = '$status_baru' WHERE Id_Laundry = '$id_laundry'");
    }
    
    // Ambil data pelanggan terkait order ini
    $q_pelanggan = mysqli_query($conn, "
        SELECT p.IdPelanggan, p.Nama, p.NoTelp, l.Total 
        FROM Laundry l 
        JOIN Pelanggan p ON l.Id_Pelanggan = p.IdPelanggan 
        WHERE l.Id_Laundry = '$id_laundry'
    ");
    $pelanggan = mysqli_fetch_assoc($q_pelanggan);

    // Catat pesan chat sistem ke pelanggan (tampil di halaman chat pelanggan)
    if ($pelanggan) {
        require_once '../functions/notifikasi.php';
        simpanChatStatus($conn, $id_laundry, $pelanggan['IdPelanggan'], $status_baru, (float)$pelanggan['Total']);
    }

    // Kirim WA
    if ($pelanggan && !empty($pelanggan['NoTelp'])) {
        $nama = $pelanggan['Nama'];
        $nomor = $pelanggan['NoTelp'];
        $total = (float)$pelanggan['Total'];
        $pesan = buatPesanStatus($nama, $id_laundry, $status_baru, $total);
        require_once '../functions/whatsapp.php';
        kirimWaFonnte($nomor, $pesan);
    }
    
    header("Location: manajemen_order.php?notif=status_ok");
    exit();
}

// =========================================================
// PROSES UPDATE BERAT & TOTAL
// =========================================================
if (isset($_POST['update_kg'])) {
    $id_laundry = (int)$_POST['id_laundry'];
    $kg_input   = (float)$_POST['kg_laundry'];
    $harga_per_kg = (float)$_POST['harga_per_kg'];

    if ($kg_input > 0 && $harga_per_kg > 0) {
        $total_baru = $kg_input * $harga_per_kg;
        mysqli_query($conn, "UPDATE Laundry SET Berat_KG = '$kg_input', Total = '$total_baru' WHERE Id_Laundry = '$id_laundry'");
        header("Location: manajemen_order.php?notif=kg_ok");
        exit();
    }
    header("Location: manajemen_order.php?notif=gagal_kg");
    exit();
}

// =========================================================
// PROSES TAMBAH PENGGUNAAN STOK MANUAL
// =========================================================
if (isset($_POST['tambah_penggunaan_stok'])) {
    $id_laundry = (int)$_POST['id_laundry'];
    $id_stok = (int)$_POST['id_stok'];
    $jumlah_pakai = (float)$_POST['jumlah_pakai'];
    $satuan_pakai = mysqli_real_escape_string($conn, $_POST['satuan_pakai']);
    $keterangan_tambahan = mysqli_real_escape_string($conn, $_POST['keterangan_tambahan']);

    $q = mysqli_query($conn, "SELECT jumlah, satuan FROM stok WHERE id_stok = $id_stok");
    $stok = mysqli_fetch_assoc($q);
    if (!$stok) {
        header("Location: manajemen_order.php?notif=stok_tidak_ditemukan");
        exit();
    }

    $stok_sekarang = (float)$stok['jumlah'];
    $satuan_stok = strtolower(trim($stok['satuan']));
    $satuan_pakai = strtolower(trim($satuan_pakai));

    // Konversi ke satuan stok
    if ($satuan_pakai != $satuan_stok) {
        $jumlah_ml = $jumlah_pakai;
        if ($satuan_pakai == 'liter' || $satuan_pakai == 'kg') {
            $jumlah_ml = $jumlah_pakai * 1000;
        }
        if ($satuan_stok == 'liter' || $satuan_stok == 'kg') {
            $jumlah_dalam_satuan_stok = $jumlah_ml / 1000;
        } elseif ($satuan_stok == 'ml') {
            $jumlah_dalam_satuan_stok = $jumlah_ml;
        } else {
            $jumlah_dalam_satuan_stok = $jumlah_pakai;
        }
    } else {
        $jumlah_dalam_satuan_stok = $jumlah_pakai;
        if ($satuan_stok == 'liter' || $satuan_stok == 'kg') {
            $jumlah_ml = $jumlah_pakai * 1000;
        } else {
            $jumlah_ml = $jumlah_pakai;
        }
    }

    if ($stok_sekarang < $jumlah_dalam_satuan_stok) {
        $error = "Stok tidak mencukupi! Tersisa: $stok_sekarang $satuan_stok";
        header("Location: manajemen_order.php?notif=stok_gagal&pesan=" . urlencode($error));
        exit();
    }

    $stok_baru = $stok_sekarang - $jumlah_dalam_satuan_stok;
    mysqli_query($conn, "UPDATE stok SET jumlah = '$stok_baru' WHERE id_stok = '$id_stok'");

    $keterangan = "Laundry ID #$id_laundry - " . $keterangan_tambahan;
    mysqli_query($conn, "INSERT INTO penggunaan_stok (id_stok, jumlah, keterangan) VALUES ('$id_stok', '$jumlah_ml', '$keterangan')");

    header("Location: manajemen_order.php?notif=pakai_stok_ok");
    exit();
}

// =========================================================
// PROSES HAPUS ORDER
// =========================================================
if (isset($_GET['hapus_order'])) {
    $id = (int)$_GET['hapus_order'];
    mysqli_query($conn, "DELETE FROM Laundry WHERE Id_Laundry = '$id'");
    header("Location: manajemen_order.php?notif=hapus_ok");
    exit();
}

// =========================================================
// AMBIL DATA JENIS SERVIS
// =========================================================
$daftar_servis = [];
$q_servis = mysqli_query($conn, "SELECT * FROM jenis_servis WHERE aktif = 1 ORDER BY id_servis ASC");
if (mysqli_num_rows($q_servis) > 0) {
    while ($s = mysqli_fetch_assoc($q_servis)) $daftar_servis[] = $s;
}
if (empty($daftar_servis)) {
    $daftar_servis = [
        ['nama_servis' => 'Cuci Setrika – 1 Hari', 'harga_per_kg' => 7500],
        ['nama_servis' => 'Cuci Setrika – 2 Hari', 'harga_per_kg' => 6500],
        ['nama_servis' => 'Cuci Setrika – 3 Hari', 'harga_per_kg' => 5500],
        ['nama_servis' => 'Cuci Setrika – 4 Hari', 'harga_per_kg' => 4500],
        ['nama_servis' => 'Cuci Kering – 1 Hari', 'harga_per_kg' => 7000],
        ['nama_servis' => 'Cuci Kering – 2 Hari', 'harga_per_kg' => 6000],
        ['nama_servis' => 'Setrika – 1 Hari', 'harga_per_kg' => 5000],
        ['nama_servis' => 'Setrika – 2 Hari', 'harga_per_kg' => 4000],
    ];
}

// Ambil daftar stok untuk dropdown
$q_stok_dropdown = mysqli_query($conn, "SELECT id_stok, nama_item, satuan FROM stok ORDER BY nama_item");

$notif = isset($_GET['notif']) ? $_GET['notif'] : '';
$pesan_error = isset($_GET['pesan']) ? $_GET['pesan'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Order - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== STYLE DASAR ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 32px; color: #1E293B; }
        .brand i { color: #0066FF; font-size: 22px; }
        .menu-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .menu-item.active a { background: #0066FF; color: white; }
        .menu-item a:hover { background: #F1F5F9; color: #1E293B; }
        .btn-logout { background: #FFE4E6; color: #E11D48; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: block; }
        .main-content { flex: 1; padding: 40px; }
        .page-title { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 6px; }
        .page-sub   { font-size: 13px; color: #64748B; margin-bottom: 24px; }
        .notif-box { padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .notif-ok  { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .notif-err { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
        .notif-warn { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
        .table-container { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px; color: #64748B; font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #E2E8F0; background: #F8FAFC; white-space: nowrap; }
        td { padding: 14px; font-size: 13.5px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
        .status-pill { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .s-baru     { background: #E0F2FE; color: #0369A1; }
        .s-proses   { background: #FEF3C7; color: #D97706; }
        .s-selesai  { background: #DCFCE7; color: #166534; }
        .s-diantar  { background: #F3E8FF; color: #7C3AED; }
        .s-default  { background: #F1F5F9; color: #475569; }
        .ctrl { padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: white; }
        .ctrl-num { width: 80px; padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; text-align: center; }
        .btn-save { padding: 7px 16px; background: #0066FF; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .btn-save:hover { background: #0052CC; }
        .btn-save-green { background: #10B981; }
        .btn-save-green:hover { background: #059669; }
        .btn-danger-sm { padding: 5px 10px; background: #EF4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700; text-decoration: none; }
        .btn-danger-sm:hover { background: #DC2626; }
        .kg-section { display: flex; flex-direction: column; gap: 4px; }
        .kg-row { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .total-display { font-size: 13px; font-weight: 800; color: #0066FF; margin-top: 3px; }
        .kg-label { font-size: 11px; color: #94A3B8; font-weight: 600; }
        .form-inline { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .input-date-small { padding: 4px 6px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px; width: 140px; }
        .stok-manual-form { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-top: 6px; padding-top: 6px; border-top: 1px dashed #E2E8F0; }
        .stok-manual-form select, .stok-manual-form input { padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px; }
        .stok-manual-form select { min-width: 120px; }
        .stok-manual-form input[type="number"] { width: 80px; }
        .stok-manual-form input[type="text"] { width: 100px; }
        .stok-manual-form .btn-sm { padding: 4px 12px; font-size: 11px; border-radius: 6px; border: none; font-weight: 700; cursor: pointer; }
        .btn-purple { background: #8B5CF6; color: white; }
        .btn-purple:hover { background: #7C3AED; }
        .stok-riwayat { font-size: 10px; color: #94A3B8; margin-top: 4px; line-height: 1.6; }
        .satuan-label { font-size: 11px; color: #64748B; font-weight: 600; margin-left: 2px; }
        .stok-form-wrapper { display: flex; flex-direction: column; gap: 4px; }
        @media (max-width: 768px) {
            .sidebar { width: 200px; padding: 16px; }
            .main-content { padding: 20px; }
            table { font-size: 12px; }
            td, th { padding: 8px; }
            .stok-manual-form { flex-direction: column; align-items: stretch; }
            .stok-manual-form select, .stok-manual-form input { width: 100%; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <p class="page-title"><i class="fa-solid fa-list-check"></i> Manajemen Order</p>
    <p class="page-sub">Kelola status, berat KG, total biaya, dan catat penggunaan stok.</p>

    <?php if ($notif === 'status_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Status order berhasil diperbarui!</div>
    <?php elseif ($notif === 'kg_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Berat KG & total berhasil disimpan!</div>
    <?php elseif ($notif === 'pakai_stok_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Penggunaan stok berhasil dicatat!</div>
    <?php elseif ($notif === 'stok_gagal'): ?>
        <div class="notif-box notif-err"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($pesan_error); ?></div>
    <?php elseif ($notif === 'stok_tidak_ditemukan'): ?>
        <div class="notif-box notif-err"><i class="fa-solid fa-circle-exclamation"></i> Stok yang dipilih tidak ditemukan.</div>
    <?php elseif ($notif === 'hapus_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Order berhasil dihapus.</div>
    <?php elseif ($notif === 'gagal_kg'): ?>
        <div class="notif-box notif-err"><i class="fa-solid fa-circle-exclamation"></i> Gagal menyimpan berat. Pastikan berat dan harga diisi.</div>
    <?php endif; ?>

    <div class="table-container">
        <h3 style="font-size:16px; color:#1E293B; font-weight:700; margin-bottom:16px;">
            <i class="fa-solid fa-list-ul"></i> Daftar Order
            <span style="font-size:12px; color:#94A3B8; font-weight:600; margin-left:12px;">
                <?php
                $q_count = mysqli_query($conn, "SELECT COUNT(*) as c FROM Laundry WHERE Status NOT IN ('Selesai')");
                $r_count = mysqli_fetch_assoc($q_count);
                echo $r_count['c'] . ' order aktif';
                ?>
            </span>
        </h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pelanggan</th>
                    <th>Tgl Masuk</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th>Estimasi Selesai</th>
                    <th>Input Berat & Servis</th>
                    <th>Total Harga</th>
                    <th>Ubah Status</th>
                    <th>Catat Penggunaan Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query = mysqli_query($conn, "
                SELECT L.*, P.Nama, P.Alamat, P.NoTelp
                FROM Laundry L
                JOIN Pelanggan P ON L.Id_Pelanggan = P.IdPelanggan
                ORDER BY L.Id_Laundry DESC
            ");

            $statuses = ['Baru', 'Diterima', 'Pickup', 'Proses Cuci', 'Proses Setrika', 'Selesai Proses', 'Diantar', 'Selesai'];

            $no = 1;
            while ($row = mysqli_fetch_assoc($query)):
                $st = strtolower($row['Status']);
                if (strpos($st, 'baru') !== false || strpos($st, 'diterima') !== false) $badge = 's-baru';
                elseif (strpos($st, 'proses') !== false || strpos($st, 'pickup') !== false) $badge = 's-proses';
                elseif ($st === 'selesai') $badge = 's-selesai';
                elseif (strpos($st, 'diantar') !== false) $badge = 's-diantar';
                else $badge = 's-default';

                $kg_tersimpan = isset($row['Berat_KG']) ? (float)$row['Berat_KG'] : 0;
                $total_tersimpan = (float)$row['Total'];
                $hpk_default = ($kg_tersimpan > 0 && $total_tersimpan > 0) ? round($total_tersimpan / $kg_tersimpan) : 6500;

                $tanggal_keluar = $row['Tanggal_Keluar'] ? date('Y-m-d', strtotime($row['Tanggal_Keluar'])) : '';
            ?>
                <tr>
                    <td style="color:#94A3B8; font-weight:700;"><?php echo $no++; ?></td>
                    <td>
                        <strong style="color:#1E293B;"><?php echo htmlspecialchars($row['Nama']); ?></strong><br>
                        <span style="font-size:11px; color:#94A3B8;"><?php echo htmlspecialchars($row['NoTelp']); ?></span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($row['Tanggal_Masuk'])); ?></td>
                    <td style="max-width:120px; font-size:12px; color:#64748B;"><?php echo htmlspecialchars($row['Catatan'] ?? '-'); ?></td>
                    <td><span class="status-pill <?php echo $badge; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                    <td>
                        <form action="" method="POST" class="form-inline">
                            <input type="hidden" name="id_laundry" value="<?php echo $row['Id_Laundry']; ?>">
                            <input type="date" name="tanggal_keluar" class="input-date-small" value="<?php echo $tanggal_keluar; ?>">
                            <button type="submit" name="update_status" class="btn-save" style="font-size:11px; padding:4px 10px;">Set</button>
                        </form>
                    </td>
                    <!-- Input Berat & Servis -->
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="id_laundry" value="<?php echo $row['Id_Laundry']; ?>">
                            <div class="kg-section">
                                <span class="kg-label">Jenis Servis</span>
                                <select name="harga_per_kg" class="ctrl">
                                    <?php foreach ($daftar_servis as $s):
                                        $hpk = isset($s['harga_per_kg']) ? (float)$s['harga_per_kg'] : 0;
                                        $sel = ($hpk == $hpk_default) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $hpk; ?>" <?php echo $sel; ?>>
                                            <?php echo htmlspecialchars($s['nama_servis']); ?>
                                            (Rp <?php echo number_format($hpk, 0, ',', '.'); ?>/Kg)
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <span class="kg-label" style="margin-top:4px;">Berat (Kg)</span>
                                <div class="kg-row">
                                    <input type="number" name="kg_laundry" step="0.1" min="0.1"
                                           class="ctrl-num"
                                           value="<?php echo $kg_tersimpan > 0 ? $kg_tersimpan : ''; ?>"
                                           placeholder="0.0">
                                    <span style="font-size:12px; color:#64748B; font-weight:600;">Kg</span>
                                    <button type="submit" name="update_kg" class="btn-save btn-save-green">
                                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                                    </button>
                                </div>

                                <div class="total-display">
                                    <?php if ($kg_tersimpan > 0): ?>
                                        Rp <?php echo number_format($total_tersimpan, 0, ',', '.'); ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </td>
                    <!-- Total Harga -->
                    <td>
                        <span style="font-weight:800; color:#1E293B; font-size:14px;">
                            Rp <?php echo number_format($total_tersimpan, 0, ',', '.'); ?>
                        </span><br>
                        <?php if ($kg_tersimpan > 0): ?>
                            <span style="font-size:11px; color:#94A3B8;"><?php echo $kg_tersimpan; ?> Kg tercatat</span>
                        <?php else: ?>
                            <span style="font-size:11px; color:#F97316; font-weight:600;">Belum ditimbang</span>
                        <?php endif; ?>
                    </td>
                    <!-- Ubah Status -->
                    <td>
                        <form action="" method="POST" class="form-inline">
                            <input type="hidden" name="id_laundry" value="<?php echo $row['Id_Laundry']; ?>">
                            <select name="status_laundry" class="ctrl">
                                <?php foreach ($statuses as $st_opt):
                                    $sel = ($row['Status'] == $st_opt) ? 'selected' : '';
                                    echo "<option value='$st_opt' $sel>$st_opt</option>";
                                endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn-save">
                                <i class="fa-solid fa-check"></i> Update
                            </button>
                        </form>
                    </td>
                    <!-- Catat Penggunaan Stok -->
                    <td>
                        <div class="stok-form-wrapper">
                            <form action="" method="POST" class="stok-manual-form">
                                <input type="hidden" name="id_laundry" value="<?php echo $row['Id_Laundry']; ?>">
                                <select name="id_stok" required>
                                    <option value="">Pilih Stok</option>
                                    <?php
                                    mysqli_data_seek($q_stok_dropdown, 0);
                                    while ($s = mysqli_fetch_assoc($q_stok_dropdown)) {
                                        echo "<option value='{$s['id_stok']}'>{$s['nama_item']} (Satuan: {$s['satuan']})</option>";
                                    }
                                    ?>
                                </select>
                                <input type="number" step="0.001" name="jumlah_pakai" placeholder="Jumlah" required>
                                <select name="satuan_pakai" required>
                                    <option value="ml">ml</option>
                                    <option value="liter">Liter</option>
                                    <option value="kg">Kg</option>
                                    <option value="pcs">Pcs</option>
                                    <option value="gram">Gram</option>
                                </select>
                                <input type="text" name="keterangan_tambahan" placeholder="Ket (opsional)">
                                <button type="submit" name="tambah_penggunaan_stok" class="btn-sm btn-purple">
                                    <i class="fa-solid fa-plus"></i> Pakai
                                </button>
                            </form>
                            <?php
                            $q_riwayat_stok = mysqli_query($conn, "
                                SELECT p.*, s.nama_item, s.satuan 
                                FROM penggunaan_stok p 
                                JOIN stok s ON p.id_stok = s.id_stok 
                                WHERE p.keterangan LIKE '%Laundry ID #{$row['Id_Laundry']}%'
                                ORDER BY p.tanggal DESC LIMIT 3
                            ");
                            if (mysqli_num_rows($q_riwayat_stok) > 0): ?>
                                <div class="stok-riwayat">
                                    <?php while ($r = mysqli_fetch_assoc($q_riwayat_stok)): 
                                        $sat = strtolower(trim($r['satuan']));
                                        $jml = (float)$r['jumlah'];
                                        if ($sat == 'liter' || $sat == 'kg') $jml = $jml / 1000;
                                        $fmt = ($sat == 'ml') ? number_format($jml, 0, ',', '.') : number_format($jml, 3, ',', '.');
                                    ?>
                                        <div>• <?= htmlspecialchars($r['nama_item']) ?>: <?= $fmt ?> <?= $r['satuan'] ?></div>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <a href="?hapus_order=<?php echo $row['Id_Laundry']; ?>" class="btn-danger-sm" onclick="return confirm('Yakin hapus order ini?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>