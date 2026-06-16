<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ================== TAMBAH DATA ==================
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['namaJenis']);
    $harga = (int)$_POST['hargaSatuan'];
    $estimasi = mysqli_real_escape_string($conn, $_POST['estimasiHari']);

    $sql = "INSERT INTO jenis_laundry (namaJenis, hargaSatuan, estimasiHari) 
            VALUES ('$nama', $harga, '$estimasi')";
    
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Jenis laundry berhasil ditambahkan!";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}

// ================== UPDATE DATA ==================
if (isset($_POST['update'])) {
    $id = (int)$_POST['idJenis'];
    $nama = mysqli_real_escape_string($conn, $_POST['namaJenis']);
    $harga = (int)$_POST['hargaSatuan'];
    $estimasi = mysqli_real_escape_string($conn, $_POST['estimasiHari']);

    $sql = "UPDATE jenis_laundry SET namaJenis='$nama', hargaSatuan=$harga, estimasiHari='$estimasi' 
            WHERE idJenis=$id";
    
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Data berhasil diupdate!";
    } else {
        $message = "❌ Error Update: " . mysqli_error($conn);
    }
}

// ================== HAPUS DATA ==================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if (mysqli_query($conn, "DELETE FROM jenis_laundry WHERE idJenis = $id")) {
        $message = "✅ Data berhasil dihapus!";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}

// ================== DATA UNTUK EDIT ==================
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM jenis_laundry WHERE idJenis = $id");
    if ($result) $edit_data = mysqli_fetch_assoc($result);
}

// ================== AMBIL DATA ==================
$query = mysqli_query($conn, "SELECT * FROM jenis_laundry ORDER BY idJenis DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jenis Laundry - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F8FAFC; display: flex; min-height: 100vh; }
        
        /* SIDEBAR */
        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; color: #1E293B; margin-bottom: 40px; }
        .brand i { color: #0066FF; font-size: 28px; }
        .menu-list { list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; margin-bottom: 4px; }
        .menu-item.active a { background: #0066FF; color: white; }
        .menu-item a:hover:not(.active a) { background: #F1F5F9; }

        /* MAIN CONTENT */
        .main-content { flex: 1; padding: 40px; }
        .card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        th { background: #F8FAFC; font-weight: 600; color: #374151; }
        .btn { padding: 10px 20px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; }
        .btn-tambah { background: #0066FF; color: white; }
        .btn-update { background: #10B981; color: white; }
        .action-btn { padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; }
        .edit-btn { background: #3B82F6; color: white; }
        .hapus-btn { background: #EF4444; color: white; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-soap"></i>
                <span>ILHAM LAUNDRY</span>
            </div>
            <ul class="menu-list">
                <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
                <li class="menu-item active"><a href="manajemen_jenis_laundry.php"><i class="fa-solid fa-tags"></i> Jenis Laundry</a></li>
                <li class="menu-item"><a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
                <li class="menu-item"><a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a></li>
            </ul>
        </div>
        <a href="logout.php" class="btn" style="background:#FEE2E2; color:#EF4444; text-align:center; text-decoration:none;">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2 style="margin-bottom: 24px; color: #1E293B;">Manajemen Jenis Laundry</h2>

        <?php if($message): ?>
            <div style="padding:16px; background:#DCFCE7; color:#166534; border-radius:12px; margin-bottom:20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- FORM TAMBAH / EDIT -->
        <div class="card">
            <h3 style="margin-bottom: 20px;"><?= $edit_data ? 'Ubah Jenis Laundry' : 'Tambah Jenis Laundry Baru' ?></h3>
            <form method="POST">
                <?php if($edit_data): ?>
                    <input type="hidden" name="idJenis" value="<?= $edit_data['idJenis'] ?>">
                <?php endif; ?>

                <input type="text" name="namaJenis" placeholder="Nama Jenis Laundry" 
                       value="<?= htmlspecialchars($edit_data['namaJenis'] ?? '') ?>" required style="width:100%; padding:14px; margin-bottom:12px; border:1px solid #E5E7EB; border-radius:12px;">

                <input type="number" name="hargaSatuan" placeholder="Harga Satuan (Rp)" 
                       value="<?= $edit_data['hargaSatuan'] ?? '' ?>" required style="width:100%; padding:14px; margin-bottom:12px; border:1px solid #E5E7EB; border-radius:12px;">

                <input type="text" name="estimasiHari" placeholder="Estimasi Hari (contoh: 3 Hari)" 
                       value="<?= htmlspecialchars($edit_data['estimasiHari'] ?? '') ?>" required style="width:100%; padding:14px; margin-bottom:20px; border:1px solid #E5E7EB; border-radius:12px;">

                <?php if($edit_data): ?>
                    <button type="submit" name="update" class="btn btn-update">Simpan Perubahan</button>
                    <a href="manajemen_jenis_laundry.php" style="margin-left:15px; color:#666; text-decoration:none;">Batal</a>
                <?php else: ?>
                    <button type="submit" name="tambah" class="btn btn-tambah">Tambah Jenis Laundry</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABEL DATA -->
        <div class="card">
            <h3 style="margin-bottom: 16px;">Daftar Jenis Laundry</h3>
            <table>
                <tr>
                    <th>idJenis</th>
                    <th>namaJenis</th>
                    <th>hargaSatuan</th>
                    <th>estimasiHari</th>
                    <th>Aksi</th>
                </tr>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= $row['idJenis'] ?></td>
                        <td><?= htmlspecialchars($row['namaJenis']) ?></td>
                        <td>Rp <?= number_format($row['hargaSatuan']) ?></td>
                        <td><?= htmlspecialchars($row['estimasiHari']) ?></td>
                        <td>
                            <a href="?edit=<?= $row['idJenis'] ?>" class="action-btn edit-btn">Ubah</a>
                            <a href="?hapus=<?= $row['idJenis'] ?>" class="action-btn hapus-btn" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:#666;">Belum ada data jenis laundry.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>