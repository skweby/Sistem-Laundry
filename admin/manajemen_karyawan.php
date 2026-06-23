<?php
session_start();
require_once '../config/database.php';

// Proteksi: hanya admin yang boleh
if (!isset($_SESSION['user_logged']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// =========================================================
// PROSES TAMBAH KARYAWAN (dengan username & password)
// =========================================================
if (isset($_POST['tambah_karyawan'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_karyawan']));
    $alamat = mysqli_real_escape_string($conn, trim($_POST['alamat']));
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $no_telp = mysqli_real_escape_string($conn, trim($_POST['no_telp']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($nama) || empty($alamat) || empty($role) || empty($no_telp) || empty($username) || empty($password)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        // Cek username sudah ada atau belum
        $cek = mysqli_query($conn, "SELECT Id_Karyawan FROM karyawan WHERE username = '$username'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username '$username' sudah digunakan!";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO karyawan (Nama_Karyawan, Alamat, Role, No_Telp_Karyawan, username, password) 
                      VALUES ('$nama', '$alamat', '$role', '$no_telp', '$username', '$password_hash')";
            if (mysqli_query($conn, $query)) {
                $sukses = "Karyawan berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan karyawan: " . mysqli_error($conn);
            }
        }
    }
}

// =========================================================
// PROSES EDIT KARYAWAN (password opsional)
// =========================================================
if (isset($_POST['edit_karyawan'])) {
    $id = (int)$_POST['id_karyawan'];
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_karyawan']));
    $alamat = mysqli_real_escape_string($conn, trim($_POST['alamat']));
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $no_telp = mysqli_real_escape_string($conn, trim($_POST['no_telp']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($nama) || empty($alamat) || empty($role) || empty($no_telp) || empty($username)) {
        $error = "Nama, alamat, role, no telp, dan username wajib diisi!";
    } else {
        // Cek username tidak bertabrakan dengan karyawan lain
        $cek = mysqli_query($conn, "SELECT Id_Karyawan FROM karyawan WHERE username = '$username' AND Id_Karyawan != '$id'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username '$username' sudah digunakan oleh karyawan lain!";
        } else {
            // Update data dasar
            $query = "UPDATE karyawan SET 
                      Nama_Karyawan = '$nama', 
                      Alamat = '$alamat', 
                      Role = '$role', 
                      No_Telp_Karyawan = '$no_telp',
                      username = '$username'";
            // Jika password diisi, update password
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password = '$password_hash'";
            }
            $query .= " WHERE Id_Karyawan = '$id'";
            
            if (mysqli_query($conn, $query)) {
                $sukses = "Karyawan berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui karyawan: " . mysqli_error($conn);
            }
        }
    }
}

// =========================================================
// PROSES HAPUS KARYAWAN
// =========================================================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM karyawan WHERE Id_Karyawan = '$id'");
    header("Location: manajemen_karyawan.php?notif=hapus_ok");
    exit();
}

// =========================================================
// AMBIL DATA KARYAWAN
// =========================================================
$query_karyawan = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY Id_Karyawan DESC");

// Data untuk edit (jika ada)
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $r = mysqli_query($conn, "SELECT * FROM karyawan WHERE Id_Karyawan = '$edit_id'");
    $edit_data = mysqli_fetch_assoc($r);
}

$notif = isset($_GET['notif']) ? $_GET['notif'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Karyawan - Admin</title>
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

        /* ── GRID ── */
        .two-col { display: grid; grid-template-columns: 420px 1fr; gap: 24px; align-items: start; }

        /* ── CARD ── */
        .card { background: white; padding: 24px 28px; border-radius: 16px; border: 1px solid #E2E8F0; }
        .card-title { font-size: 15px; font-weight: 800; color: #1E293B; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .card-title i { color: #0066FF; }

        /* ── FORM ── */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
        .input-box { width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0; border-radius: 10px; font-size: 14px; color: #1E293B; outline: none; transition: 0.2s; }
        .input-box:focus { border-color: #0066FF; box-shadow: 0 0 0 3px rgba(0,102,255,0.08); }
        select.input-box { cursor: pointer; }
        .btn-primary { padding: 10px 20px; background: #0066FF; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { background: #0052CC; }
        .btn-green { background: #10B981; }
        .btn-green:hover { background: #059669; }
        .btn-red { background: #EF4444; color: white; }
        .btn-red:hover { background: #DC2626; }
        .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
        .btn-edit { background: #EFF6FF; color: #0066FF; }
        .btn-edit:hover { background: #DBEAFE; }
        .btn-del { background: #FEF2F2; color: #EF4444; }
        .btn-del:hover { background: #FEE2E2; }

        /* ── TABLE ── */
        .table-karyawan { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .table-karyawan thead th { padding: 10px 12px; font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 2px solid #E2E8F0; background: #F8FAFC; text-align: left; }
        .table-karyawan tbody td { padding: 12px 12px; font-size: 13.5px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
        .table-karyawan tbody tr:last-child td { border-bottom: none; }
        .table-karyawan tbody tr:hover { background: #FAFAFA; }

        .role-pill { background: #E0F2FE; color: #0369A1; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
        .empty-row td { text-align: center; color: #94A3B8; padding: 32px; font-size: 13px; }
        .edit-mode { border: 2px solid #0066FF; }
        .edit-mode .card-title { color: #0066FF; }
        .btn-batal { background: #F1F5F9; color: #475569; text-decoration: none; }
        .btn-batal:hover { background: #E2E8F0; }
        .note-password { font-size: 11px; color: #94A3B8; margin-top: 4px; font-style: italic; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="brand"><i class="fa-solid fa-soap"></i><span>RIFFANASH LAUNDRY</span></div>
        <ul class="menu-list">
            <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
            <li class="menu-item"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
            <li class="menu-item active"><a href="manajemen_karyawan.php"><i class="fa-solid fa-user-tie"></i> Manajemen Karyawan</a></li>
            <li class="menu-item"><a href="laporan_transaksi.php"><i class="fa-solid fa-coins"></i> Laporan Transaksi</a></li>
            <li class="menu-item"><a href="manajemen_stok.php"><i class="fa-solid fa-boxes-stacked"></i> Manajemen Stok</a></li>
            <li class="menu-item"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
        </ul>
    </div>
    <a href="logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main-content">
    <p class="page-title"><i class="fa-solid fa-user-tie"></i> Manajemen Karyawan</p>
    <p class="page-sub">Kelola data karyawan laundry, termasuk username dan password untuk login.</p>

    <?php if (isset($sukses)): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> <?= $sukses ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="notif-box notif-err"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if ($notif === 'hapus_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Karyawan berhasil dihapus!</div>
    <?php endif; ?>

    <div class="two-col">
        <!-- FORM TAMBAH / EDIT KARYAWAN -->
        <div class="card <?php echo $edit_data ? 'edit-mode' : ''; ?>">
            <div class="card-title">
                <i class="fa-solid <?php echo $edit_data ? 'fa-pen-to-square' : 'fa-user-plus'; ?>"></i>
                <?php echo $edit_data ? 'Edit Karyawan' : 'Tambah Karyawan Baru'; ?>
            </div>
            <form action="" method="POST">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_karyawan" value="<?php echo $edit_data['Id_Karyawan']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nama Karyawan <span style="color:red;">*</span></label>
                    <input type="text" name="nama_karyawan" class="input-box"
                           placeholder="Masukkan nama karyawan"
                           value="<?php echo $edit_data ? htmlspecialchars($edit_data['Nama_Karyawan']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Alamat <span style="color:red;">*</span></label>
                    <input type="text" name="alamat" class="input-box"
                           placeholder="Masukkan alamat karyawan"
                           value="<?php echo $edit_data ? htmlspecialchars($edit_data['Alamat']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Role / Jabatan <span style="color:red;">*</span></label>
                    <select name="role" class="input-box" required>
                        <option value="">-- Pilih Role --</option>
                        <?php
                        $roles = ['Kasir', 'Kurir', 'Pencuci', 'Setrika', 'Supervisor'];
                        $role_cur = $edit_data['Role'] ?? '';
                        foreach ($roles as $r) {
                            $sel = ($role_cur == $r) ? 'selected' : '';
                            echo "<option value='$r' $sel>$r</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon <span style="color:red;">*</span></label>
                    <input type="tel" name="no_telp" class="input-box"
                           placeholder="Contoh: 08123456789"
                           value="<?php echo $edit_data ? htmlspecialchars($edit_data['No_Telp_Karyawan']) : ''; ?>" required>
                </div>

                <!-- ===== TAMBAHAN USERNAME & PASSWORD ===== -->
                <div class="form-group">
                    <label>Username <span style="color:red;">*</span></label>
                    <input type="text" name="username" class="input-box"
                           placeholder="Masukkan username untuk login"
                           value="<?php echo $edit_data ? htmlspecialchars($edit_data['username']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Password <?php echo $edit_data ? '<span style="color:#F59E0B;">(kosongkan jika tidak diubah)</span>' : '<span style="color:red;">*</span>'; ?></label>
                    <input type="password" name="password" class="input-box"
                           placeholder="<?php echo $edit_data ? 'Kosongkan jika tidak diubah' : 'Masukkan password'; ?>"
                           <?php echo $edit_data ? '' : 'required'; ?>>
                    <?php if ($edit_data): ?>
                        <div class="note-password">🔒 Biarkan kosong jika tidak ingin mengubah password.</div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="submit" name="<?php echo $edit_data ? 'edit_karyawan' : 'tambah_karyawan'; ?>" class="btn-primary">
                        <i class="fa-solid <?php echo $edit_data ? 'fa-pen-to-square' : 'fa-plus'; ?>"></i>
                        <?php echo $edit_data ? 'Perbarui Karyawan' : 'Tambah Karyawan'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="manajemen_karyawan.php" class="btn-primary btn-batal" style="text-decoration: none; padding: 10px 20px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABEL DAFTAR KARYAWAN -->
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-list"></i> Daftar Karyawan</div>
            <table class="table-karyawan">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Role</th>
                        <th>No Telp</th>
                        <th>Username</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $ada_data = false;
                    while ($row = mysqli_fetch_assoc($query_karyawan)):
                        $ada_data = true;
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong style="color:#1E293B;"><?= htmlspecialchars($row['Nama_Karyawan']) ?></strong></td>
                            <td style="font-size:12px; color:#64748B;"><?= htmlspecialchars($row['Alamat']) ?></td>
                            <td><span class="role-pill"><?= htmlspecialchars($row['Role']) ?></span></td>
                            <td><?= htmlspecialchars($row['No_Telp_Karyawan']) ?></td>
                            <td><code style="background:#F1F5F9; padding:2px 8px; border-radius:4px; font-size:12px;"><?= htmlspecialchars($row['username']) ?></code></td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="?edit=<?= $row['Id_Karyawan'] ?>" class="btn-sm btn-edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="?hapus=<?= $row['Id_Karyawan'] ?>" class="btn-sm btn-del"
                                       onclick="return confirm('Yakin hapus karyawan <?= htmlspecialchars($row['Nama_Karyawan']) ?>?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (!$ada_data): ?>
                        <tr class="empty-row">
                            <td colspan="7">
                                <i class="fa-solid fa-user-slash" style="font-size:24px; display:block; margin-bottom:8px; color:#CBD5E1;"></i>
                                Belum ada data karyawan. Tambahkan di form kiri.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>