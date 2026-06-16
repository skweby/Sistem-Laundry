<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

// =========================================================
// PROSES UPDATE STATUS ORDER
// =========================================================
if (isset($_POST['update_status'])) {
    $id_laundry  = (int)$_POST['id_laundry'];
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_laundry']);
    mysqli_query($conn, "UPDATE Laundry SET Status = '$status_baru' WHERE Id_Laundry = '$id_laundry'");
    header("Location: manajemen_order.php?notif=status_ok");
    exit();
// Proses Update Status Order dan Tanggal Keluar
if (isset($_POST['update_status'])) {
    $id_laundry = $_POST['id_laundry'];
    $status_baru = $_POST['status_laundry'];
    $tanggal_keluar = !empty($_POST['tanggal_keluar']) ? $_POST['tanggal_keluar'] : NULL;

    if ($tanggal_keluar) {
        $query = "UPDATE Laundry SET Status = '$status_baru', Tanggal_Keluar = '$tanggal_keluar' WHERE Id_Laundry = '$id_laundry'";
    } else {
        $query = "UPDATE Laundry SET Status = '$status_baru' WHERE Id_Laundry = '$id_laundry'";
    }
    mysqli_query($conn, $query);
}

// ======================================================
// PROSES UPDATE KG & HITUNG ULANG TOTAL
// ======================================================
if (isset($_POST['update_kg'])) {
    $id_laundry = (int)$_POST['id_laundry'];
    $kg_input   = (float)$_POST['kg_laundry'];
    $harga_per_kg = (float)$_POST['harga_per_kg'];

    if ($kg_input > 0 && $harga_per_kg > 0) {
        $total_baru = $kg_input * $harga_per_kg;
        mysqli_query($conn, "UPDATE Laundry SET Berat_KG = '$kg_input', Total = '$total_baru' WHERE Id_Laundry = '$id_laundry'");
    }
    header("Location: manajemen_order.php?notif=kg_ok");
    exit();
}

// =========================================================
// AMBIL DATA JENIS SERVIS UNTUK DROPDOWN HARGA/KG
// (dari tabel jenis_servis jika ada, fallback ke array default)
// =========================================================
$daftar_servis = [];
$q_servis = mysqli_query($conn, "SHOW TABLES LIKE 'jenis_servis'");
if (mysqli_num_rows($q_servis) > 0) {
    $res = mysqli_query($conn, "SELECT * FROM jenis_servis ORDER BY id_servis ASC");
    while ($s = mysqli_fetch_assoc($res)) {
        $daftar_servis[] = $s;
    }
}
// Fallback jika tabel belum ada / kosong
if (empty($daftar_servis)) {
    $daftar_servis = [
        ['nama_servis' => 'Cuci Setrika – 1 Hari',  'harga_per_kg' => 7500],
        ['nama_servis' => 'Cuci Setrika – 2 Hari',  'harga_per_kg' => 6500],
        ['nama_servis' => 'Cuci Setrika – 3 Hari',  'harga_per_kg' => 5500],
        ['nama_servis' => 'Cuci Setrika – 4 Hari',  'harga_per_kg' => 4500],
        ['nama_servis' => 'Cuci Kering – 1 Hari',   'harga_per_kg' => 7000],
        ['nama_servis' => 'Cuci Kering – 2 Hari',   'harga_per_kg' => 6000],
        ['nama_servis' => 'Setrika – 1 Hari',        'harga_per_kg' => 5000],
        ['nama_servis' => 'Setrika – 2 Hari',        'harga_per_kg' => 4000],
    ];
}

// Encode untuk JavaScript
$servis_json = json_encode($daftar_servis);

// Notifikasi
$notif = isset($_GET['notif']) ? $_GET['notif'] : '';
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
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 32px; color: #1E293B; }
        .brand i { color: #0066FF; font-size: 22px; }
        .menu-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .menu-item.active a { background: #0066FF; color: white; }
        .menu-item a:hover { background: #F1F5F9; color: #1E293B; }
        .btn-logout { background: #FFE4E6; color: #E11D48; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: block; }

        /* ── MAIN ── */
        .main-content { flex: 1; padding: 40px; overflow-x: auto; }
        .page-title { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 6px; }
        .page-sub   { font-size: 13px; color: #64748B; margin-bottom: 24px; }

        /* ── NOTIF ── */
        .notif-box { padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .notif-ok  { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }

        /* ── TABLE ── */
        .table-container { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; }
        .table-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        thead th { padding: 12px 14px; color: #64748B; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #E2E8F0; background: #F8FAFC; }
        tbody td { padding: 14px; font-size: 13.5px; color: #334155; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #F8FAFC; }

        /* ── STATUS BADGE ── */
        .status-pill { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .s-baru     { background: #E0F2FE; color: #0369A1; }
        .s-proses   { background: #FEF3C7; color: #D97706; }
        .s-selesai  { background: #DCFCE7; color: #166534; }
        .s-diantar  { background: #F3E8FF; color: #7C3AED; }
        .s-default  { background: #F1F5F9; color: #475569; }

        /* ── FORM CONTROLS ── */
        .form-inline { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        select.ctrl { padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; color: #334155; background: white; cursor: pointer; }
        input.ctrl-num { width: 72px; padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; color: #334155; text-align: center; }
        .btn-save   { padding: 7px 14px; background: #0066FF; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .btn-save:hover { background: #0052CC; }
        .btn-save-green { background: #10B981; }
        .btn-save-green:hover { background: #059669; }

        /* ── KG SECTION ── */
        .kg-section { display: flex; flex-direction: column; gap: 4px; }
        .kg-row { display: flex; gap: 6px; align-items: center; }
        .total-display { font-size: 13px; font-weight: 800; color: #0066FF; margin-top: 3px; }
        .kg-label { font-size: 11px; color: #94A3B8; font-weight: 600; }
        .harga-servis-info { font-size: 11px; color: #64748B; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #E2E8F0; font-size: 14px; }
        select { padding: 6px; border-radius: 6px; border: 1px solid #CBD5E1; font-weight: 600; }
        .btn-update { padding: 6px 12px; background: #0066FF; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .input-date-small { padding: 4px 6px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 12px; width: 140px; }
        .form-inline { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand"><i class="fa-solid fa-soap"></i><span>ILHAM LAUNDRY</span></div>
        <ul class="menu-list">
            <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item active"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
            <li class="menu-item"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
            <li class="menu-item"><a href="laporan_transaksi.php"><i class="fa-solid fa-coins"></i> Laporan Transaksi</a></li>
            <li class="menu-item"><a href="manajemen_stok.php"><i class="fa-solid fa-boxes-stacked"></i> Manajemen Stok</a></li>
            <li class="menu-item"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
        </ul>
    </div>
    <a href="logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main-content">
    <p class="page-title"><i class="fa-solid fa-list-check"></i> Manajemen Order</p>
    <p class="page-sub">Kelola status, berat KG, dan total biaya setiap order pelanggan.</p>

    <?php if ($notif === 'status_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Status order berhasil diperbarui!</div>
    <?php elseif ($notif === 'kg_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Berat KG & total harga berhasil disimpan!</div>
    <?php endif; ?>

    <div class="table-container">
        <div class="table-header">
            <h3 style="font-size:16px; color:#1E293B; font-weight:700;">Antrean Order Masuk</h3>
            <span style="font-size:12px; color:#94A3B8; font-weight:600;">
                <?php
                $q_count = mysqli_query($conn, "SELECT COUNT(*) as c FROM Laundry WHERE Status NOT IN ('Selesai')");
                $r_count = mysqli_fetch_assoc($q_count);
                echo $r_count['c'] . ' order aktif';
                ?>
            </span>
    <div class="main-content">
        <div class="table-container">
            <h2><i class="fa-solid fa-list-check"></i> Antrean Manajemen Order</h2>
            <table>
                <thead>
                    <tr>
                        <th>NAMA PELANGGAN</th>
                        <th>ALAMAT</th>
                        <th>TANGGAL MASUK</th>
                        <th>ESTIMASI SELESAI</th>
                        <th>STATUS SEKARANG</th>
                        <th>AKSI UBAH STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($conn, "SELECT L.*, P.Nama, P.Alamat FROM Laundry L JOIN Pelanggan P ON L.Id_Pelanggan = P.IdPelanggan ORDER BY L.Id_Laundry DESC");
                    while ($row = mysqli_fetch_assoc($query)) {
                        $statuses = ['Diterima', 'Pickup', 'Proses Cuci', 'Proses Setrika', 'Selesai Proses', 'Diantar', 'Selesai'];
                        $tanggal_keluar = $row['Tanggal_Keluar'] ? date('Y-m-d', strtotime($row['Tanggal_Keluar'])) : '';
                        echo "<tr>
                            <td><strong>{$row['Nama']}</strong></td>
                            <td>{$row['Alamat']}</td>
                            <td>{$row['Tanggal_Masuk']}</td>
                            <td>
                                <form action='' method='POST' class='form-inline'>
                                    <input type='hidden' name='id_laundry' value='{$row['Id_Laundry']}'>
                                    <input type='date' name='tanggal_keluar' class='input-date-small' value='{$tanggal_keluar}'>
                            </td>
                            <td>
                                    <select name='status_laundry'>";
                                    foreach ($statuses as $st) {
                                        $selected = ($row['Status'] == $st) ? 'selected' : '';
                                        echo "<option value='$st' $selected>$st</option>";
                                    }
                                    echo "</select>
                            </td>
                            <td>
                                    <button type='submit' name='update_status' class='btn-update'>Simpan</button>
                                </form>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pelanggan</th>
                    <th>Tgl Masuk</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th>Input KG & Servis</th>
                    <th>Total Harga</th>
                    <th>Ubah Status</th>
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
                // Tentukan class badge status
                $st = strtolower($row['Status']);
                if (strpos($st, 'baru') !== false || strpos($st, 'diterima') !== false) $badge = 's-baru';
                elseif (strpos($st, 'proses') !== false || strpos($st, 'pickup') !== false) $badge = 's-proses';
                elseif ($st === 'selesai') $badge = 's-selesai';
                elseif (strpos($st, 'diantar') !== false) $badge = 's-diantar';
                else $badge = 's-default';

                // KG & Total yang tersimpan
                $kg_tersimpan = isset($row['Berat_KG']) ? (float)$row['Berat_KG'] : 0;
                $total_tersimpan = (float)$row['Total'];
                // Harga per KG awal (ambil dari Total/KG jika keduanya ada)
                $hpk_default = ($kg_tersimpan > 0 && $total_tersimpan > 0) ? round($total_tersimpan / $kg_tersimpan) : 6500;
            ?>
                <tr>
                    <td style="color:#94A3B8; font-weight:700;"><?php echo $no++; ?></td>
                    <td>
                        <strong style="color:#1E293B;"><?php echo htmlspecialchars($row['Nama']); ?></strong><br>
                        <span style="font-size:11px; color:#94A3B8;"><?php echo htmlspecialchars($row['NoTelp']); ?></span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($row['Tanggal_Masuk'])); ?></td>
                    <td style="max-width:130px; font-size:12px; color:#64748B;"><?php echo htmlspecialchars($row['Catatan'] ?? '-'); ?></td>
                    <td><span class="status-pill <?php echo $badge; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>

                    <!-- ── INPUT KG & SERVIS ── -->
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="id_laundry" value="<?php echo $row['Id_Laundry']; ?>">
                            <div class="kg-section">
                                <!-- Pilih Jenis Servis -->
                                <span class="kg-label">Jenis Servis</span>
                                <select name="harga_per_kg" class="ctrl"
                                        id="servis_<?php echo $row['Id_Laundry']; ?>"
                                        onchange="hitungTotal(<?php echo $row['Id_Laundry']; ?>)">
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

                                <!-- Input KG -->
                                <span class="kg-label" style="margin-top:4px;">Berat (Kg)</span>
                                <div class="kg-row">
                                    <input type="number" name="kg_laundry" step="0.1" min="0.1"
                                           id="kg_<?php echo $row['Id_Laundry']; ?>"
                                           class="ctrl-num"
                                           value="<?php echo $kg_tersimpan > 0 ? $kg_tersimpan : ''; ?>"
                                           placeholder="0.0"
                                           onchange="hitungTotal(<?php echo $row['Id_Laundry']; ?>)"
                                           oninput="hitungTotal(<?php echo $row['Id_Laundry']; ?>)">
                                    <span style="font-size:12px; color:#64748B; font-weight:600;">Kg</span>
                                    <button type="submit" name="update_kg" class="btn-save btn-save-green">
                                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                                    </button>
                                </div>

                                <!-- Preview total real-time -->
                                <div class="total-display" id="preview_<?php echo $row['Id_Laundry']; ?>">
                                    <?php if ($kg_tersimpan > 0): ?>
                                        Rp <?php echo number_format($total_tersimpan, 0, ',', '.'); ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </td>

                    <!-- Total Tersimpan di DB -->
                    <td>
                        <span style="font-weight:800; color:#1E293B; font-size:14px;" id="total_db_<?php echo $row['Id_Laundry']; ?>">
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
                        <form action="" method="POST">
                            <input type="hidden" name="id_laundry" value="<?php echo $row['Id_Laundry']; ?>">
                            <div class="form-inline">
                                <select name="status_laundry" class="ctrl">
                                    <?php foreach ($statuses as $st_opt):
                                        $sel = ($row['Status'] == $st_opt) ? 'selected' : '';
                                        echo "<option value='$st_opt' $sel>$st_opt</option>";
                                    endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn-save">
                                    <i class="fa-solid fa-check"></i> Update
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const servisData = <?php echo $servis_json; ?>;

function hitungTotal(idLaundry) {
    const selectEl = document.getElementById('servis_' + idLaundry);
    const kgEl     = document.getElementById('kg_' + idLaundry);
    const previewEl = document.getElementById('preview_' + idLaundry);

    const hargaPerKg = parseFloat(selectEl.value) || 0;
    const kg         = parseFloat(kgEl.value) || 0;
    const total      = hargaPerKg * kg;

    if (total > 0) {
        previewEl.textContent = '→ Rp ' + total.toLocaleString('id-ID');
        previewEl.style.color = '#10B981';
    } else {
        previewEl.textContent = '—';
        previewEl.style.color = '#0066FF';
    }
}
</script>

</body>
</html>