<?php
session_start();
require_once '../config/database.php';

// Proteksi halaman admin
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

// Tambah / Update
if (isset($_POST['simpan_jenis'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga_kg = floatval($_POST['harga_kg']);
    $harga_pcs = floatval($_POST['harga_pcs']);
    $estimasi_hari = intval($_POST['estimasi_hari']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if ($id > 0) {
        // Update
        $sql = "UPDATE jenis_laundry SET 
                nama_jenis='$nama', kategori='$kategori', 
                harga_kg='$harga_kg', harga_pcs='$harga_pcs', 
                estimasi_hari='$estimasi_hari', status='$status' 
                WHERE id_jenis=$id";
    } else {
        // Insert
        $sql = "INSERT INTO jenis_laundry (nama_jenis, kategori, harga_kg, harga_pcs, estimasi_hari, status) 
                VALUES ('$nama', '$kategori', '$harga_kg', '$harga_pcs', '$estimasi_hari', '$status')";
    }
    
    if (mysqli_query($conn, $sql)) {
        $pesan = $id > 0 ? "Jenis laundry berhasil diupdate!" : "Jenis laundry berhasil ditambahkan!";
        echo "<script>alert('$pesan'); window.location='manajemen_jenis_laundry.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM jenis_laundry WHERE id_jenis=$id");
    echo "<script>alert('Jenis laundry dihapus!'); window.location='manajemen_jenis_laundry.php';</script>";
}

// Ambil data untuk edit
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM jenis_laundry WHERE id_jenis=$id");
    $edit = mysqli_fetch_assoc($result);
}

// Ambil semua data
$result = mysqli_query($conn, "SELECT * FROM jenis_laundry ORDER BY nama_jenis");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jenis Laundry - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Gunakan style serupa dengan admin/index.php atau HTML mockup Anda */
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background:#f8fafc; display:flex; min-height:100vh; }
        .sidebar { width:260px; background:white; border-right:1px solid #e2e8f0; padding:24px; }
        .main-content { flex:1; padding:30px; }
        table { width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding:12px 16px; text-align:left; border-bottom:1px solid #e2e8f0; }
        th { background:#f1f5f9; font-weight:600; color:#475569; }
        .btn { padding:8px 16px; border:none; border-radius:8px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
        .btn-primary { background:#185FA5; color:white; }
        .btn-danger { background:#ef4444; color:white; }
        .modal { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:25px; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.2); width:480px; z-index:1000; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999; }
        .modal.open, .modal-overlay.open { display:block; }
    </style>
</head>
<body>

<div class="sidebar">
    <!-- Sidebar sama seperti admin/index.php -->
    <div class="brand"><i class="fa-solid fa-soap"></i> ILHAM LAUNDRY</div>
    <ul class="menu-list">
        <li class="menu-item"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li class="menu-item"><a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a></li>
        <li class="menu-item active"><a href="manajemen_jenis_laundry.php"><i class="fa-solid fa-shirt"></i> Jenis Laundry</a></li>
        <!-- tambahkan menu lain -->
    </ul>
</div>

<div class="main-content">
    <h1>Manajemen Jenis Laundry</h1>
    <button class="btn btn-primary" onclick="openModal()">+ Tambah Jenis Baru</button>

    <table>
        <thead>
            <tr>
                <th>Nama Jenis</th>
                <th>Kategori</th>
                <th>Harga/Kg</th>
                <th>Harga/Pcs</th>
                <th>Estimasi Hari</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_jenis']) ?></td>
                <td><?= htmlspecialchars($row['kategori']) ?></td>
                <td>Rp <?= number_format($row['harga_kg'],0) ?></td>
                <td>Rp <?= number_format($row['harga_pcs'],0) ?></td>
                <td><?= $row['estimasi_hari'] ?> hari</td>
                <td><span class="badge <?= $row['status']=='Aktif'?'badge-green':'badge-gray' ?>"><?= $row['status'] ?></span></td>
                <td>
                    <a href="?edit=<?= $row['id_jenis'] ?>" class="btn" style="background:#eab308;color:white">Edit</a>
                    <a href="?hapus=<?= $row['id_jenis'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeModal()">
    <div class="modal" id="modalJenis">
        <h3><?= $edit ? 'Edit' : 'Tambah' ?> Jenis Laundry</h3>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit['id_jenis'] ?? '' ?>">
            <label>Nama Jenis</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($edit['nama_jenis'] ?? '') ?>" required>
            
            <label>Kategori</label>
            <select name="kategori">
                <option value="Kiloan" <?= ($edit['kategori']??'')=='Kiloan'?'selected':'' ?>>Kiloan</option>
                <option value="Satuan" <?= ($edit['kategori']??'')=='Satuan'?'selected':'' ?>>Satuan</option>
            </select>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
                <div>
                    <label>Harga per Kg (Rp)</label>
                    <input type="number" name="harga_kg" value="<?= $edit['harga_kg'] ?? 0 ?>">
                </div>
                <div>
                    <label>Harga per Pcs (Rp)</label>
                    <input type="number" name="harga_pcs" value="<?= $edit['harga_pcs'] ?? 0 ?>">
                </div>
            </div>

            <label>Estimasi (hari)</label>
            <input type="number" name="estimasi_hari" value="<?= $edit['estimasi_hari'] ?? 2 ?>" required>

            <label>Status</label>
            <select name="status">
                <option value="Aktif" <?= ($edit['status']??'')=='Aktif'?'selected':'' ?>>Aktif</option>
                <option value="Nonaktif" <?= ($edit['status']??'')=='Nonaktif'?'selected':'' ?>>Nonaktif</option>
            </select>

            <div style="margin-top:20px">
                <button type="button" class="btn" onclick="closeModal()">Batal</button>
                <button type="submit" name="simpan_jenis" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() { 
    document.getElementById('modalOverlay').classList.add('open'); 
}
function closeModal() { 
    document.getElementById('modalOverlay').classList.remove('open'); 
}
</script>
</body>
</html>