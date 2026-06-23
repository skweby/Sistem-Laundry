<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: ../login_admin.php");
    exit();
}

// ====================== CRUD: DELETE ORDER ======================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM Laundry WHERE Id_Laundry = $id");
    header("Location: laporan_periodik.php?notif=deleted");
    exit();
}

// ====================== CRUD: UPDATE ORDER ======================
if (isset($_POST['update_transaksi'])) {
    $id          = (int)$_POST['id_laundry'];
    $berat       = (float)$_POST['berat_kg'];
    $total       = (float)$_POST['total'];
    $tgl_keluar  = mysqli_real_escape_string($conn, $_POST['tanggal_keluar']);

    mysqli_query($conn, "UPDATE Laundry SET Berat_Kg = '$berat', Total = '$total', Tanggal_Keluar = '$tgl_keluar' WHERE Id_Laundry = $id");
    header("Location: laporan_periodik.php?notif=updated&tgl_awal=" . $_POST['tgl_awal'] . "&tgl_akhir=" . $_POST['tgl_akhir']);
    exit();
}

// ====================== FILTER TANGGAL ======================
$tgl_awal  = isset($_GET['tgl_awal'])  ? $_GET['tgl_awal']  : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// ====================== AMBIL DATA TRANSAKSI SELESAI ======================
$query = mysqli_query($conn, "
    SELECT L.*, P.Nama as Nama_Pelanggan 
    FROM Laundry L 
    JOIN Pelanggan P ON L.Id_Pelanggan = P.IdPelanggan 
    WHERE L.Status = 'Selesai' 
      AND L.Tanggal_Masuk BETWEEN '$tgl_awal' AND '$tgl_akhir'
    ORDER BY L.Tanggal_Masuk DESC
");

// Hitung total pendapatan dan data untuk chart
$total_pendapatan = 0;
$data_chart = [];
while ($row = mysqli_fetch_assoc($query)) {
    $total_pendapatan += (float)$row['Total'];
    $tgl = date('Y-m-d', strtotime($row['Tanggal_Masuk']));
    if (!isset($data_chart[$tgl])) $data_chart[$tgl] = 0;
    $data_chart[$tgl] += (float)$row['Total'];
}
// Kembalikan pointer ke awal agar bisa di-loop lagi
mysqli_data_seek($query, 0);

$labels = json_encode(array_keys($data_chart));
$values = json_encode(array_values($data_chart));

$notif = isset($_GET['notif']) ? $_GET['notif'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Periodik RIFFANASH LAUNDRY</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #0066FF; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F8FAFC; display: flex; min-height: 100vh; }

        .sidebar { width: 260px; background: white; border-right: 1px solid #E2E8F0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; position: fixed; height: 100vh; overflow-y: auto; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 32px; color: #1E293B; }
        .brand i { color: #0066FF; font-size: 22px; }
        .menu-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .menu-item a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .menu-item.active a { background: #0066FF; color: white; }
        .menu-item a:hover { background: #F1F5F9; color: #1E293B; }
        .btn-logout { background: #FFE4E6; color: #E11D48; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: block; }

        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        .card { background: white; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-size: 22px; font-weight: 700; color: #1E293B; }
        .page-header p { color: #64748B; font-size: 14px; margin-top: 4px; }

        .notif { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .notif-success { background: #D1FAE5; color: #065F46; }
        .notif-danger  { background: #FEE2E2; color: #991B1B; }

        .filter-form { display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }
        .filter-form label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .filter-form input[type="date"] { padding: 8px 12px; border: 1px solid #E2E8F0; border-radius: 8px; font-family: inherit; font-size: 14px; color: #1E293B; }
        .btn-primary { background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-family: inherit; font-weight: 600; font-size: 14px; cursor: pointer; }
        .btn-primary:hover { background: #0052CC; }

        .total-amount { font-size: 2.5rem; font-weight: 700; color: #1E40AF; margin-top: 8px; }
        .total-label { color: #64748B; font-size: 14px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #E2E8F0; font-size: 14px; }
        th { background: #F1F5F9; font-weight: 600; color: #475569; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #F8FAFC; }

        .action-group { display: flex; gap: 6px; }
        .btn-edit   { background: #EFF6FF; color: #1D4ED8; padding: 6px 10px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-edit:hover { background: #DBEAFE; }
        .btn-danger { background: #FEE2E2; color: #DC2626; padding: 6px 10px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-danger:hover { background: #FECACA; }

        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 460px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
        .modal-box h3 { font-size: 18px; font-weight: 700; color: #1E293B; margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #E2E8F0; border-radius: 8px; font-family: inherit; font-size: 14px; color: #1E293B; }
        .form-group input:focus { outline: none; border-color: #0066FF; box-shadow: 0 0 0 3px rgba(0,102,255,0.1); }
        .modal-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save { background: #0066FF; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-family: inherit; font-weight: 600; font-size: 14px; cursor: pointer; flex: 1; }
        .btn-save:hover { background: #0052CC; }
        .btn-cancel { background: #F1F5F9; color: #475569; padding: 10px 20px; border: none; border-radius: 8px; font-family: inherit; font-weight: 600; font-size: 14px; cursor: pointer; flex: 1; }
        .btn-cancel:hover { background: #E2E8F0; }

        @media (max-width: 768px) {
            .sidebar { width: 200px; padding: 16px; }
            .main-content { margin-left: 200px; padding: 16px; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="fa-solid fa-chart-bar" style="color:#0066FF; margin-right:8px;"></i>Laporan Periodik</h2>
        <p>RIFFANASH LAUNDRY &bull; Rekap pendapatan berdasarkan periode</p>
    </div>

    <!-- Notifikasi -->
    <?php if ($notif === 'deleted'): ?>
        <div class="notif notif-danger"><i class="fa-solid fa-trash-can"></i> Transaksi berhasil dihapus.</div>
    <?php elseif ($notif === 'updated'): ?>
        <div class="notif notif-success"><i class="fa-solid fa-circle-check"></i> Transaksi berhasil diperbarui.</div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card">
        <form method="GET" class="filter-form">
            <div>
                <label>Tanggal Awal</label>
                <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
            </div>
            <div>
                <label>Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
            </div>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
        </form>
    </div>

    <!-- Total Pendapatan -->
    <div class="card">
        <div class="total-label">Total Pendapatan Periode <?= date('d M Y', strtotime($tgl_awal)) ?> &ndash; <?= date('d M Y', strtotime($tgl_akhir)) ?></div>
        <div class="total-amount">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
    </div>

    <!-- Grafik Penjualan -->
    <div class="card">
        <h3 style="margin-bottom:16px;">📈 Grafik Penjualan Harian</h3>
        <canvas id="salesChart" height="100"></canvas>
    </div>

    <!-- Tabel Transaksi Selesai -->
    <div class="card">
        <h3 style="margin-bottom:16px;">🧾 Daftar Transaksi Selesai</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID Order</th>
                    <th>Pelanggan</th>
                    <th>Tgl Masuk</th>
                    <th>Tgl Selesai</th>
                    <th>Berat (Kg)</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $no = 1;
            // Reset pointer hasil query
            mysqli_data_seek($query, 0);
            while ($row = mysqli_fetch_assoc($query)): 
                $tgl_keluar_val = $row['Tanggal_Keluar'] ? date('Y-m-d', strtotime($row['Tanggal_Keluar'])) : '';
                // Ambil berat dengan aman
                $berat = isset($row['Berat_Kg']) ? (float)$row['Berat_Kg'] : 0;
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong>#<?= $row['Id_Laundry'] ?></strong></td>
                    <td><?= htmlspecialchars($row['Nama_Pelanggan']) ?></td>
                    <td><?= date('d M Y', strtotime($row['Tanggal_Masuk'])) ?></td>
                    <td><?= $tgl_keluar_val ? date('d M Y', strtotime($tgl_keluar_val)) : '-' ?></td>
                    <td>
                        <?php if ($berat > 0): ?>
                            <?= number_format($berat, 1, ',', '.') ?> Kg
                        <?php else: ?>
                            <span style="color:#F97316; font-weight:600;">-</span>
                        <?php endif; ?>
                    </td>
                    <td><strong>Rp <?= number_format($row['Total'], 0, ',', '.') ?></strong></td>
                    <td>
                        <div class="action-group">
                            <button class="btn-edit" onclick="openEditModal(
                                <?= $row['Id_Laundry'] ?>,
                                '<?= htmlspecialchars($row['Nama_Pelanggan']) ?>',
                                '<?= $tgl_keluar_val ?>',
                                <?= $berat ?>,
                                <?= (float)$row['Total'] ?>
                            )">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                            <a href="?hapus=<?= $row['Id_Laundry'] ?>&tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" 
                               class="btn-danger" 
                               onclick="return confirm('Hapus transaksi #<?= $row['Id_Laundry'] ?> ini?')"
                               style="text-decoration:none;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($query) == 0): ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:#94A3B8;">
                        <i class="fa-solid fa-inbox" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                        Tidak ada data transaksi selesai pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== MODAL EDIT TRANSAKSI ===== -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <h3><i class="fas fa-pen" style="color:#0066FF; margin-right:8px;"></i>Edit Transaksi</h3>
        <form method="POST">
            <input type="hidden" name="update_transaksi" value="1">
            <input type="hidden" name="id_laundry" id="edit_id">
            <input type="hidden" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
            <input type="hidden" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">

            <div class="form-group">
                <label>Pelanggan</label>
                <input type="text" id="edit_nama" readonly style="background:#F8FAFC; color:#94A3B8;">
            </div>
            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_keluar" id="edit_tgl_keluar">
            </div>
            <div class="form-group">
                <label>Berat (Kg)</label>
                <input type="number" name="berat_kg" id="edit_berat" step="0.1" min="0">
            </div>
            <div class="form-group">
                <label>Total (Rp)</label>
                <input type="number" name="total" id="edit_total" step="500" min="0">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
// ===== CHART =====
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= $labels ?>,
        datasets: [{
            label: 'Pendapatan Harian',
            data: <?= $values ?>,
            borderColor: '#0066FF',
            backgroundColor: 'rgba(0, 102, 255, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#0066FF',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => 'Rp ' + Number(v).toLocaleString('id-ID')
                }
            }
        }
    }
});

// ===== MODAL =====
function openEditModal(id, nama, tglKeluar, berat, total) {
    document.getElementById('edit_id').value        = id;
    document.getElementById('edit_nama').value      = nama;
    document.getElementById('edit_tgl_keluar').value = tglKeluar;
    document.getElementById('edit_berat').value     = berat;
    document.getElementById('edit_total').value     = total;
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}

// Tutup modal jika klik di luar box
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

</body>
</html>