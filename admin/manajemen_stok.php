<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: login.php");
    exit();
}

// Proses Tambah Stok
if (isset($_POST['tambah_stok'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_item']);
    $jumlah = (float) $_POST['jumlah'];
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $harga = (float) $_POST['harga_satuan'];

    $query = "INSERT INTO stok (nama_item, jumlah, satuan, harga_satuan) VALUES ('$nama', $jumlah, '$satuan', $harga)";
    if (mysqli_query($conn, $query)) {
        $sukses = "Stok berhasil ditambahkan.";
    } else {
        $error = "Gagal menambahkan stok: " . mysqli_error($conn);
    }
}

// Hapus Stok
if (isset($_GET['hapus_stok'])) {
    $id = (int) $_GET['hapus_stok'];
    mysqli_query($conn, "DELETE FROM stok WHERE id_stok = $id");
    header("Location: manajemen_stok.php");
    exit();
}

// Ambil data stok
$query_stok = mysqli_query($conn, "SELECT * FROM stok ORDER BY id_stok DESC");
// Ambil riwayat penggunaan (join dengan satuan)
$query_penggunaan = mysqli_query($conn, "
    SELECT p.*, s.nama_item, s.satuan 
    FROM penggunaan_stok p 
    JOIN stok s ON p.id_stok = s.id_stok 
    ORDER BY p.tanggal DESC LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Stok - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .card-box { background: white; padding: 20px 24px; border-radius: 16px; border: 1px solid #E2E8F0; margin-bottom: 24px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-weight: 600; font-size: 13px; color: #475569; margin-bottom: 4px; }
        .form-group input, .form-group select { width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 8px; }
        .btn { padding: 8px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-primary { background: #0066FF; color: white; }
        .btn-danger { background: #EF4444; color: white; }
        .btn-success { background: #10B981; color: white; }
        .btn-warning { background: #F59E0B; color: white; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .alert-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 10px; }
        th { padding: 10px; color: #64748B; font-size: 13px; font-weight: 600; border-bottom: 1px solid #E2E8F0; }
        td { padding: 10px; font-size: 14px; border-bottom: 1px solid #F1F5F9; }
        .stok-rendah { color: #EF4444; font-weight: 700; }
        .stok-cukup { color: #10B981; font-weight: 700; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-boxes-stacked"></i> Manajemen Stok Sabun/Deterjen</h2>

    <?php if (isset($sukses)): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= $sukses ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-exclamation-triangle"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="grid-2">
        <!-- Form Tambah Stok -->
        <div class="card-box">
            <h4><i class="fa-solid fa-plus-circle"></i> Tambah Stok Baru</h4>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Item</label>
                    <input type="text" name="nama_item" placeholder="Contoh: Deterjen Cair" required>
                </div>
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" step="0.01" name="jumlah" placeholder="Jumlah" required>
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" name="satuan" placeholder="ml, liter, kg" value="ml" required>
                </div>
                <div class="form-group">
                    <label>Harga Satuan (opsional)</label>
                    <input type="number" step="0.01" name="harga_satuan" placeholder="0" value="0">
                </div>
                <button type="submit" name="tambah_stok" class="btn btn-primary"><i class="fa-solid fa-save"></i> Tambah</button>
            </form>
        </div>

    <!-- Daftar Stok -->
    <div class="card-box">
        <h4><i class="fa-solid fa-list"></i> Daftar Stok Saat Ini</h4>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Item</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Harga Satuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($query_stok)): ?>
                <tr>
                    <td><?= $row['id_stok'] ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_item']) ?></strong></td>
                    <td class="<?= $row['jumlah'] < 50 ? 'stok-rendah' : 'stok-cukup' ?>"><?= $row['jumlah'] ?></td>
                    <td><?= $row['satuan'] ?></td>
                    <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                    <td>
                        <a href="?hapus_stok=<?= $row['id_stok'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus stok ini?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($query_stok) == 0): ?>
                <tr><td colspan="6" style="text-align:center; padding:20px;">Belum ada data stok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Riwayat Penggunaan Stok (dengan konversi satuan) -->
    <div class="card-box">
        <h4><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Penggunaan Stok (Terbaru)</h4>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Item</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($query_penggunaan)):
                    // Konversi jumlah dari ml ke satuan stok
                    $satuan = strtolower(trim($row['satuan']));
                    $jumlah_tampil = (float)$row['jumlah']; // tersimpan dalam ml
                    if ($satuan == 'liter' || $satuan == 'kg') {
                        $jumlah_tampil = $jumlah_tampil / 1000;
                        $format = number_format($jumlah_tampil, 3, ',', '.');
                    } else {
                        // ml atau lainnya
                        $format = number_format($jumlah_tampil, 0, ',', '.');
                    }
                ?>
                <tr>
                    <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_item']) ?></td>
                    <td><?= $format ?> <?= htmlspecialchars($row['satuan']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($query_penggunaan) == 0): ?>
                <tr><td colspan="4" style="text-align:center; padding:20px;">Belum ada penggunaan stok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>