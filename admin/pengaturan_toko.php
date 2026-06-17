<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_logged'])) { header("Location: login.php"); exit(); }

// =========================================================
// BUAT TABEL jenis_servis JIKA BELUM ADA
// =========================================================
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS jenis_servis (
        id_servis    INT AUTO_INCREMENT PRIMARY KEY,
        nama_servis  VARCHAR(100)   NOT NULL,
        harga_per_kg DECIMAL(10,2)  NOT NULL DEFAULT 0,
        satuan       ENUM('kg','pcs','pasang') NOT NULL DEFAULT 'kg',
        keterangan   VARCHAR(200)   DEFAULT '',
        aktif        TINYINT(1)     NOT NULL DEFAULT 1,
        created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$notif  = '';
$notif_type = 'ok';

// =========================================================
// PROSES: TAMBAH JENIS SERVIS BARU
// =========================================================
if (isset($_POST['tambah_servis'])) {
    $nama   = mysqli_real_escape_string($conn, trim($_POST['nama_servis']));
    $harga  = (float)$_POST['harga_per_kg'];
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $ket    = mysqli_real_escape_string($conn, trim($_POST['keterangan']));

    if (empty($nama) || $harga <= 0) {
        $notif = "Nama servis dan harga wajib diisi!";
        $notif_type = 'err';
    } else {
        $cek = mysqli_query($conn, "SELECT id_servis FROM jenis_servis WHERE nama_servis = '$nama'");
        if (mysqli_num_rows($cek) > 0) {
            $notif = "Jenis servis dengan nama tersebut sudah ada!";
            $notif_type = 'err';
        } else {
            mysqli_query($conn, "INSERT INTO jenis_servis (nama_servis, harga_per_kg, satuan, keterangan) VALUES ('$nama', '$harga', '$satuan', '$ket')");
            $notif = "Jenis servis berhasil ditambahkan!";
        }
    }
}

// =========================================================
// PROSES: EDIT JENIS SERVIS
// =========================================================
if (isset($_POST['edit_servis'])) {
    $id    = (int)$_POST['id_servis'];
    $nama  = mysqli_real_escape_string($conn, trim($_POST['nama_servis']));
    $harga = (float)$_POST['harga_per_kg'];
    $satuan= mysqli_real_escape_string($conn, $_POST['satuan']);
    $ket   = mysqli_real_escape_string($conn, trim($_POST['keterangan']));
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    mysqli_query($conn, "UPDATE jenis_servis SET nama_servis='$nama', harga_per_kg='$harga', satuan='$satuan', keterangan='$ket', aktif='$aktif' WHERE id_servis='$id'");
    $notif = "Jenis servis berhasil diperbarui!";
}

// =========================================================
// PROSES: HAPUS JENIS SERVIS
// =========================================================
if (isset($_GET['hapus_servis'])) {
    $id = (int)$_GET['hapus_servis'];
    mysqli_query($conn, "DELETE FROM jenis_servis WHERE id_servis = '$id'");
    header("Location: pengaturan_toko.php?notif=hapus_ok");
    exit();
}

// =========================================================
// PROSES: PENGATURAN TOKO (STATUS & JAM)
// =========================================================
if (isset($_POST['save_settings'])) {
    $status_toko = mysqli_real_escape_string($conn, $_POST['status_toko']);
    $jam_buka    = mysqli_real_escape_string($conn, $_POST['jam_buka']);
    $_SESSION['status_toko']   = $status_toko;
    $_SESSION['jam_operasional'] = $jam_buka;
    $notif = "Pengaturan toko berhasil disimpan!";
}

if (isset($_GET['notif']) && $_GET['notif'] === 'hapus_ok') {
    $notif = "Jenis servis berhasil dihapus.";
}

// Ambil data servis
$q_servis = mysqli_query($conn, "SELECT * FROM jenis_servis ORDER BY id_servis DESC");

// Data edit (jika ada request)
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $r = mysqli_query($conn, "SELECT * FROM jenis_servis WHERE id_servis = '$edit_id'");
    $edit_data = mysqli_fetch_assoc($r);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Toko - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        .main-content { flex: 1; padding: 40px; }
        .page-title { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 6px; }
        .page-sub   { font-size: 13px; color: #64748B; margin-bottom: 24px; }

        /* ── NOTIF ── */
        .notif-box { padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .notif-ok  { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .notif-err { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

        /* ── GRID LAYOUT ── */
        .two-col { display: grid; grid-template-columns: 420px 1fr; gap: 24px; align-items: start; }

        /* ── CARD ── */
        .card { background: white; padding: 28px; border-radius: 16px; border: 1px solid #E2E8F0; }
        .card-title { font-size: 15px; font-weight: 800; color: #1E293B; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .card-title i { color: #0066FF; }

        /* ── FORM ── */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 7px; }
        .input-box { width: 100%; padding: 11px 14px; border: 1px solid #E2E8F0; border-radius: 10px; font-size: 14px; color: #1E293B; outline: none; transition: 0.2s; }
        .input-box:focus { border-color: #0066FF; box-shadow: 0 0 0 3px rgba(0,102,255,0.08); }
        select.input-box { cursor: pointer; }
        .input-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        .btn-primary { padding: 11px 20px; background: #0066FF; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { background: #0052CC; }
        .btn-green  { background: #10B981; }
        .btn-green:hover { background: #059669; }
        .btn-red    { background: #EF4444; color: white; }
        .btn-red:hover { background: #DC2626; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
        .btn-edit { background: #EFF6FF; color: #0066FF; }
        .btn-edit:hover { background: #DBEAFE; }
        .btn-del  { background: #FEF2F2; color: #EF4444; }
        .btn-del:hover { background: #FEE2E2; }

        /* ── TOGGLE AKTIF ── */
        .toggle-row { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
        .toggle-label { font-size: 13px; font-weight: 600; color: #475569; }
        input[type="checkbox"].toggle-cb { width: 18px; height: 18px; accent-color: #0066FF; cursor: pointer; }

        /* ── SERVIS TABLE ── */
        .servis-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .servis-table thead th { padding: 10px 12px; font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 2px solid #E2E8F0; background: #F8FAFC; text-align: left; }
        .servis-table tbody td { padding: 13px 12px; font-size: 13.5px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
        .servis-table tbody tr:last-child td { border-bottom: none; }
        .servis-table tbody tr:hover { background: #FAFAFA; }

        .badge-aktif { background: #DCFCE7; color: #166534; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-nonaktif { background: #F1F5F9; color: #94A3B8; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .satuan-pill { background: #EFF6FF; color: #0066FF; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }

        .empty-row td { text-align: center; color: #94A3B8; padding: 32px; font-size: 13px; }

        /* Edit mode highlight */
        .edit-mode { border: 2px solid #0066FF; }
        .edit-mode .card-title { color: #0066FF; }
    </style>
</head>
<body>
    
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <p class="page-title"><i class="fa-solid fa-gear"></i> Pengaturan Toko</p>
    <p class="page-sub">Kelola status toko, jam operasional, dan daftar jenis layanan laundry.</p>

    <?php if ($notif): ?>
        <div class="notif-box <?php echo $notif_type === 'err' ? 'notif-err' : 'notif-ok'; ?>">
            <i class="fa-solid <?php echo $notif_type === 'err' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
            <?php echo $notif; ?>
        </div>
    <?php endif; ?>

    <!-- ── BARIS ATAS: Info Toko ── -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title"><i class="fa-solid fa-store"></i> Konfigurasi Toko</div>
        <form action="" method="POST">
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

    <!-- ── BARIS BAWAH: Form Servis + Tabel ── -->
    <div class="two-col">

        <!-- FORM TAMBAH / EDIT SERVIS -->
        <div class="card <?php echo $edit_data ? 'edit-mode' : ''; ?>">
            <div class="card-title">
                <i class="fa-solid <?php echo $edit_data ? 'fa-pen-to-square' : 'fa-circle-plus'; ?>"></i>
                <?php echo $edit_data ? 'Edit Jenis Servis' : 'Tambah Jenis Servis Baru'; ?>
            </div>

            <form action="" method="POST">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_servis" value="<?php echo $edit_data['id_servis']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nama Servis <span style="color:red;">*</span></label>
                    <input type="text" name="nama_servis" class="input-box"
                           placeholder="Cth: Cuci Jas, Laundry Selimut, Cuci Sepatu..."
                           value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama_servis']) : ''; ?>" required>
                </div>

                <div class="input-row">
                    <div class="form-group">
                        <label>Harga (Rp) <span style="color:red;">*</span></label>
                        <input type="number" name="harga_per_kg" class="input-box"
                               placeholder="Cth: 7500"
                               value="<?php echo $edit_data ? $edit_data['harga_per_kg'] : ''; ?>"
                               min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <select name="satuan" class="input-box">
                            <?php
                            $satuan_opts = ['kg' => 'Per Kg', 'pcs' => 'Per Pcs / Item', 'pasang' => 'Per Pasang'];
                            $sat_cur = $edit_data['satuan'] ?? 'kg';
                            foreach ($satuan_opts as $val => $lbl) {
                                $sel = ($sat_cur === $val) ? 'selected' : '';
                                echo "<option value='$val' $sel>$lbl</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Keterangan (opsional)</label>
                    <input type="text" name="keterangan" class="input-box"
                           placeholder="Cth: Express 1 hari, khusus bahan halus..."
                           value="<?php echo $edit_data ? htmlspecialchars($edit_data['keterangan']) : ''; ?>">
                </div>

                <?php if ($edit_data): ?>
                    <div class="toggle-row">
                        <input type="checkbox" name="aktif" id="cb_aktif" class="toggle-cb"
                               <?php echo $edit_data['aktif'] ? 'checked' : ''; ?>>
                        <label for="cb_aktif" class="toggle-label">Servis Aktif (tampil di pilihan order)</label>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="submit" name="<?php echo $edit_data ? 'edit_servis' : 'tambah_servis'; ?>" class="btn-primary">
                        <i class="fa-solid <?php echo $edit_data ? 'fa-pen-to-square' : 'fa-plus'; ?>"></i>
                        <?php echo $edit_data ? 'Perbarui Servis' : 'Tambah Servis'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="pengaturan_toko.php" class="btn-primary" style="background: #F1F5F9; color: #475569; text-decoration: none;">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABEL DAFTAR SERVIS -->
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-list"></i> Daftar Jenis Servis</div>
            <table class="servis-table">
                <thead>
                    <tr>
                        <th>Nama Servis</th>
                        <th>Harga</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                mysqli_data_seek($q_servis, 0);
                $ada_data = false;
                while ($s = mysqli_fetch_assoc($q_servis)):
                    $ada_data = true;
                    $sat_lbl = ['kg' => '/Kg', 'pcs' => '/Pcs', 'pasang' => '/Pasang'];
                    $sat_disp = $sat_lbl[$s['satuan']] ?? '';
                ?>
                    <tr>
                        <td>
                            <strong style="color:#1E293B;"><?php echo htmlspecialchars($s['nama_servis']); ?></strong>
                            <?php if (!empty($s['keterangan'])): ?>
                                <br><span style="font-size:11px; color:#94A3B8;"><?php echo htmlspecialchars($s['keterangan']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:800; color:#0066FF;">
                            Rp <?php echo number_format($s['harga_per_kg'], 0, ',', '.'); ?>
                            <span style="font-weight:500; color:#94A3B8; font-size:11px;"><?php echo $sat_disp; ?></span>
                        </td>
                        <td><span class="satuan-pill"><?php echo strtoupper($s['satuan']); ?></span></td>
                        <td>
                            <span class="<?php echo $s['aktif'] ? 'badge-aktif' : 'badge-nonaktif'; ?>">
                                <?php echo $s['aktif'] ? 'Aktif' : 'Nonaktif'; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <a href="?edit=<?php echo $s['id_servis']; ?>" class="btn-sm btn-edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="?hapus_servis=<?php echo $s['id_servis']; ?>" class="btn-sm btn-del"
                                   onclick="return confirm('Yakin hapus servis ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (!$ada_data): ?>
                    <tr class="empty-row">
                        <td colspan="5">
                            <i class="fa-solid fa-inbox" style="font-size:24px; display:block; margin-bottom:8px; color:#CBD5E1;"></i>
                            Belum ada jenis servis. Tambahkan di form kiri.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /two-col -->
</div>

</body>
</html>