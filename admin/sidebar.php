<?php
// Cek role dari session
$role = $_SESSION['role'] ?? 'karyawan';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div>
        <div class="brand">
            <i class="fa-solid fa-soap"></i>
            <span>RIFFANASH LAUNDRY</span>
        </div>
        <ul class="menu-list">
            <!-- 1. DASHBOARD (semua bisa) -->
            <li class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            </li>

            <!-- 2. MANAJEMEN ORDER (semua bisa) -->
            <li class="menu-item <?php echo $current_page == 'manajemen_order.php' ? 'active' : ''; ?>">
                <a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a>
            </li>

            <?php if ($role == 'admin'): ?>
            <!-- 3. DATA PELANGGAN (hanya admin) -->
            <li class="menu-item <?php echo $current_page == 'data_pelanggan.php' ? 'active' : ''; ?>">
                <a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a>
            </li>

            <!-- 4. MANAJEMEN KARYAWAN (hanya admin) -->
            <li class="menu-item <?php echo $current_page == 'manajemen_karyawan.php' ? 'active' : ''; ?>">
                <a href="manajemen_karyawan.php"><i class="fa-solid fa-user-tie"></i> Manajemen Karyawan</a>
            </li>

            <li class="menu-item <?= $current_page === 'rating.php' ? 'active' : '' ?>">
                <a href="rating.php"><i class="fa-solid fa-star"></i> Rating Pelanggan</a>
            </li>
            
            <?php endif; ?>

            <li class="menu-item <?= $current_page === 'laporan_periodik.php' ? 'active' : '' ?>">
                <a href="laporan_periodik.php"><i class="fa-solid fa-chart-bar"></i> Laporan Periodik</a>
            </li>

            <!-- 6. MANAJEMEN STOK (semua bisa) -->
            <li class="menu-item <?php echo $current_page == 'manajemen_stok.php' ? 'active' : ''; ?>">
                <a href="manajemen_stok.php"><i class="fa-solid fa-boxes-stacked"></i> Manajemen Stok</a>
            </li>

            <!-- 7. PENGATURAN TOKO (semua bisa) -->
            <li class="menu-item <?php echo $current_page == 'pengaturan_toko.php' ? 'active' : ''; ?>">
                <a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a>
            </li>
        </ul>
    </div>
    <a href="logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>