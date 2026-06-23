<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_logged'])) { header("Location: ../login_admin.php"); exit(); }

$role = $_SESSION['role'] ?? 'karyawan';

// Hapus rating (moderasi) - hanya admin
if (isset($_GET['hapus']) && $role === 'admin') {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM rating WHERE Id_Rating = $id_hapus");
    header("Location: rating.php?notif=hapus_ok");
    exit();
}

// Filter berdasarkan jumlah bintang (opsional)
$filter_bintang = isset($_GET['bintang']) ? (int)$_GET['bintang'] : 0;
$where = '';
if ($filter_bintang >= 1 && $filter_bintang <= 5) {
    $where = "WHERE r.Jumlah_Bintang = $filter_bintang";
}

// Statistik ringkas
$q_stat = mysqli_query($conn, "SELECT COUNT(*) as total, AVG(Jumlah_Bintang) as rata FROM rating");
$r_stat = mysqli_fetch_assoc($q_stat);
$total_rating = $r_stat['total'] ?? 0;
$rata_rating  = $r_stat['rata'] ? round($r_stat['rata'], 1) : 0;

$distribusi = [];
for ($i = 5; $i >= 1; $i--) {
    $q_d = mysqli_query($conn, "SELECT COUNT(*) as c FROM rating WHERE Jumlah_Bintang = $i");
    $r_d = mysqli_fetch_assoc($q_d);
    $distribusi[$i] = $r_d['c'] ?? 0;
}

// =========================================================
// AMBIL DATA RATING + KOMENTAR
// =========================================================
$query = mysqli_query($conn, "
    SELECT r.Id_Rating, r.Jumlah_Bintang, r.Tanggal_Rating, r.Id_Laundry, r.Komentar, p.Nama, p.NoTelp
    FROM rating r
    JOIN pelanggan p ON r.Id_Pelanggan = p.IdPelanggan
    $where
    ORDER BY r.Tanggal_Rating DESC
");

$notif = isset($_GET['notif']) ? $_GET['notif'] : '';

function bintangAdmin($jumlah) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $color = $i <= $jumlah ? '#F59E0B' : '#E2E8F0';
        $html .= "<i class='fa-solid fa-star' style='color:$color; font-size:13px;'></i>";
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating Pelanggan - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        .page-title { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 6px; }
        .page-sub   { font-size: 13px; color: #64748B; margin-bottom: 24px; }

        .notif-box { padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .notif-ok  { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }

        .stats-row { display: flex; gap: 20px; margin-bottom: 28px; flex-wrap: wrap; }
        .card-avg { background: white; border: 1px solid #E2E8F0; border-radius: 16px; padding: 24px; min-width: 200px; display: flex; align-items: center; gap: 16px; }
        .card-avg .num { font-size: 32px; font-weight: 800; color: #1E293B; }
        .card-avg .label { font-size: 12px; color: #64748B; font-weight: 600; }
        .card-dist { background: white; border: 1px solid #E2E8F0; border-radius: 16px; padding: 20px 24px; flex: 1; min-width: 260px; }
        .dist-row { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; font-size: 12px; color: #475569; }
        .dist-bar-bg { flex: 1; background: #F1F5F9; border-radius: 8px; height: 8px; overflow: hidden; }
        .dist-bar-fill { background: #F59E0B; height: 100%; border-radius: 8px; }

        .filter-row { margin-bottom: 16px; display: flex; gap: 8px; flex-wrap: wrap; }
        .filter-chip { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-decoration: none; color: #64748B; background: #F1F5F9; }
        .filter-chip.active { background: #0066FF; color: white; }

        .table-container { background: white; padding: 24px; border-radius: 16px; border: 1px solid #E2E8F0; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px; color: #64748B; font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #E2E8F0; background: #F8FAFC; white-space: nowrap; }
        td { padding: 14px; font-size: 13.5px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
        .komentar-cell { max-width: 320px; color: #475569; word-break: break-word; }
        .komentar-empty { color: #CBD5E1; font-style: italic; }
        .btn-danger-sm { padding: 5px 10px; background: #EF4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700; text-decoration: none; }
        .btn-danger-sm:hover { background: #DC2626; }
        @media (max-width: 768px) {
            .sidebar { width: 200px; padding: 16px; }
            .main-content { padding: 20px; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <p class="page-title"><i class="fa-solid fa-star"></i> Rating Pelanggan</p>
    <p class="page-sub">Lihat seluruh ulasan dan rating yang diberikan pelanggan terhadap layanan laundry.</p>

    <?php if ($notif === 'hapus_ok'): ?>
        <div class="notif-box notif-ok"><i class="fa-solid fa-circle-check"></i> Rating berhasil dihapus.</div>
    <?php endif; ?>

    <div class="stats-row">
        <div class="card-avg">
            <i class="fa-solid fa-star" style="font-size:28px; color:#F59E0B;"></i>
            <div>
                <div class="num"><?php echo $rata_rating; ?></div>
                <div class="label"><?php echo $total_rating; ?> total ulasan</div>
            </div>
        </div>
        <div class="card-dist">
            <?php for ($i = 5; $i >= 1; $i--):
                $persen = $total_rating > 0 ? round(($distribusi[$i] / $total_rating) * 100) : 0;
            ?>
                <div class="dist-row">
                    <span style="width:38px;"><?php echo $i; ?> <i class="fa-solid fa-star" style="color:#F59E0B; font-size:10px;"></i></span>
                    <div class="dist-bar-bg"><div class="dist-bar-fill" style="width:<?php echo $persen; ?>%;"></div></div>
                    <span style="width:30px; text-align:right;"><?php echo $distribusi[$i]; ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="filter-row">
        <a href="rating.php" class="filter-chip <?php echo $filter_bintang === 0 ? 'active' : ''; ?>">Semua</a>
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <a href="rating.php?bintang=<?php echo $i; ?>" class="filter-chip <?php echo $filter_bintang === $i ? 'active' : ''; ?>"><?php echo $i; ?> Bintang</a>
        <?php endfor; ?>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Order</th>
                    <th>Rating</th>
                    <th>Komentar</th>
                    <th>Tanggal</th>
                    <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) === 0): ?>
                    <tr><td colspan="6" style="text-align:center; color:#94A3B8;">Belum ada rating dari pelanggan.</td></tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): 
                        $komentar = trim($row['Komentar'] ?? '');
                    ?>
                        <tr>
                            <td>
                                <strong style="color:#1E293B;"><?php echo htmlspecialchars($row['Nama']); ?></strong><br>
                                <span style="font-size:11px; color:#94A3B8;"><?php echo htmlspecialchars($row['NoTelp']); ?></span>
                            </td>
                            <td>#TRX-<?php echo $row['Id_Laundry']; ?></td>
                            <td><?php echo bintangAdmin($row['Jumlah_Bintang']); ?></td>
                            <td class="komentar-cell">
                                <?php if (!empty($komentar)): ?>
                                    <?php echo htmlspecialchars($komentar); ?>
                                <?php else: ?>
                                    <span class="komentar-empty">- tidak ada komentar -</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['Tanggal_Rating'])); ?></td>
                            <?php if ($role === 'admin'): ?>
                            <td>
                                <a href="rating.php?hapus=<?php echo $row['Id_Rating']; ?><?php echo $filter_bintang ? '&bintang='.$filter_bintang : ''; ?>" class="btn-danger-sm" onclick="return confirm('Hapus rating ini?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>