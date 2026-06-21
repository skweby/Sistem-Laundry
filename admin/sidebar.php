<?php
$role = $_SESSION['role'] ?? 'karyawan';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- SIDEBAR ADMIN -->
<div class="sidebar">
    <div class="sidebar-top">
    <div class="brand">
        <i class="fa-solid fa-soap"></i>
        <span>ILHAM LAUNDRY</span>
    </div>

    <ul class="menu-list">
        <li class="menu-item <?= $current_page === 'index.php' ? 'active' : '' ?>">
            <a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        </li>
        <li class="menu-item <?= $current_page === 'manajemen_order.php' ? 'active' : '' ?>">
            <a href="manajemen_order.php"><i class="fa-solid fa-list-check"></i> Manajemen Order</a>
        </li>

        <?php if ($role === 'admin'): ?>
        <li class="menu-item <?= $current_page === 'data_pelanggan.php' ? 'active' : '' ?>">
            <a href="data_pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a>
        </li>
        <li class="menu-item <?= $current_page === 'manajemen_karyawan.php' ? 'active' : '' ?>">
            <a href="manajemen_karyawan.php"><i class="fa-solid fa-user-tie"></i> Manajemen Karyawan</a>
        </li>
        <li class="menu-item <?= $current_page === 'laporan_transaksi.php' ? 'active' : '' ?>">
            <a href="laporan_transaksi.php"><i class="fa-solid fa-coins"></i> Laporan Transaksi</a>
        </li>
        <?php endif; ?>

        <li class="menu-item <?= $current_page === 'manajemen_stok.php' ? 'active' : '' ?>">
            <a href="manajemen_stok.php"><i class="fa-solid fa-boxes-stacked"></i> Manajemen Stok</a>
        </li>
        <li class="menu-item <?= $current_page === 'laporan_periodik.php' ? 'active' : '' ?>">
            <a href="laporan_periodik.php"><i class="fa-solid fa-chart-bar"></i> Laporan Periodik</a>
        </li>
        <li class="menu-item <?= $current_page === 'pengaturan_toko.php' ? 'active' : '' ?>">
            <a href="pengaturan_toko.php"><i class="fa-solid fa-gear"></i> Pengaturan Toko</a>
        </li>
    </ul>
    </div><!-- end sidebar-top -->

    <a href="logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>