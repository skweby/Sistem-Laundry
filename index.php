<?php
session_start();

// Ambil status filter aktif (default: cuci-setrika)
$category_active = isset($_GET['filter']) ? $_GET['filter'] : 'cuci-setrika';

/**
 * =========================================================================
 * TRIK UNCHECK RADIO BUTTON (KILOAN) BERBASIS URL SESSION / RE-CLICK
 * ==========================================================================
 */
if (isset($_GET['filter_changed'])) {
    unset($_SESSION['last_kiloan_selected']);
} else if (isset($_GET['items']['kiloan'])) {
    $current_click = $_GET['items']['kiloan'];
    if (isset($_SESSION['last_kiloan_selected']) && $_SESSION['last_kiloan_selected'] == $current_click) {
        unset($_GET['items']['kiloan']);
        unset($_SESSION['last_kiloan_selected']);
    } else {
        $_SESSION['last_kiloan_selected'] = $current_click;
    }
} else {
    unset($_SESSION['last_kiloan_selected']);
}

/**
 * SIMULASI PENGATURAN STATUS OPEN/CLOSE OLEH ADMIN
 */
if (!isset($_SESSION['status_toko'])) {
    $_SESSION['status_toko'] = 'BUKA'; 
}
$status_toko = $_SESSION['status_toko']; 

// Data Mockup Layanan Laundry
$layanan_kiloan = [
    ['id' => 1, 'category' => 'cuci-setrika', 'name' => 'Cuci Setrika – 1 Hari', 'price' => 7500, 'badge' => 'Express', 'badge_class' => 'text-express'],
    ['id' => 2, 'category' => 'cuci-setrika', 'name' => 'Cuci Setrika – 2 Hari', 'price' => 6500, 'badge' => 'Regular', 'badge_class' => ''],
    ['id' => 3, 'category' => 'cuci-setrika', 'name' => 'Cuci Setrika – 3 Hari', 'price' => 5500, 'badge' => 'Biasa', 'badge_class' => ''],
    ['id' => 4, 'category' => 'cuci-setrika', 'name' => 'Cuci Setrika – 4 Hari', 'price' => 4500, 'badge' => 'Hemat', 'badge_class' => ''],
    
    ['id' => 5, 'category' => 'cuci-kering', 'name' => 'Cuci Kering – 1 Hari', 'price' => 7000, 'badge' => 'Express', 'badge_class' => 'text-express'],
    ['id' => 6, 'category' => 'cuci-kering', 'name' => 'Cuci Kering – 2 Hari', 'price' => 6000, 'badge' => 'Regular', 'badge_class' => ''],
    
    ['id' => 7, 'category' => 'setrika', 'name' => 'Setrika – 1 Hari', 'price' => 5000, 'badge' => 'Express', 'badge_class' => 'text-express'],
    ['id' => 8, 'category' => 'setrika', 'name' => 'Setrika – 2 Hari', 'price' => 4000, 'badge' => 'Regular', 'badge_class' => '']
];

$layanan_satuan = [
    ['id' => 9, 'category' => 'bedcover', 'name' => 'Bedcover Ukuran Besar', 'price' => 35000, 'badge' => 'Satuan', 'badge_class' => ''],
    ['id' => 10, 'category' => 'sepatu', 'name' => 'Cuci Sepatu Sneaker / Sport', 'price' => 25000, 'badge' => 'Deep Clean', 'badge_class' => '']
];

$selected_items = isset($_GET['items']) ? $_GET['items'] : [];
$total_estimasi = 0;
$catatan_satuan = isset($_GET['catatan_satuan']) ? htmlspecialchars($_GET['catatan_satuan']) : '';

// Hitung total estimasi harga
foreach($layanan_kiloan as $item) {
    if (isset($selected_items['kiloan']) && $selected_items['kiloan'] == $item['id'] && $item['category'] == $category_active) {
        $total_estimasi += $item['price'];
    }
}
foreach($layanan_satuan as $item) {
    if (isset($selected_items['satuan_' . $item['id']]) && $item['category'] == $category_active) {
        $total_estimasi += $item['price'];
    }
}

/**
 * ==========================================================================
 * LOGIKA INTERSEPSI PROSES ORDER (PERBAIKAN ALUR LOGIN -> RIWAYAT)
 * ==========================================================================
 */
if (isset($_GET['tombol_order'])) {
    $catatan = isset($_GET['catatan_satuan']) ? trim($_GET['catatan_satuan']) : '';
    $items_terpilih = isset($_GET['items']) ? $_GET['items'] : [];

    if (empty($items_terpilih)) {
        $error_message = "Silakan pilih minimal satu layanan laundry terlebih dahulu sebelum melakukan order!";
    } else if (empty($catatan)) {
        $error_message = "Catatan tambahan cucian wajib diisi sebelum melakukan order!";
    } else {
        if (!isset($_SESSION['id_pelanggan'])) {
            $_SESSION['temporary_order'] = $_GET;
            header("Location: views/auth/login.php");
            exit();
        } else {
            require_once 'config/database.php';

            $id_pelanggan = $_SESSION['id_pelanggan'];
            $tanggal_masuk = date('Y-m-d');
            $status_awal = 'Baru';

            // 1. Tentukan idJenis dan idTipe
            $id_jenis_terpilih = null;
            $id_tipe = 2; // default Regular

            if (isset($items_terpilih['kiloan'])) {
                $kiloan_id = $items_terpilih['kiloan'];
                // cari badge dari data mockup
                $badge = '';
                foreach ($layanan_kiloan as $item) {
                    if ($item['id'] == $kiloan_id) {
                        $badge = $item['badge'];
                        break;
                    }
                }
                $id_tipe = ($badge == 'Express') ? 1 : 2;

                if ($kiloan_id <= 4) $id_jenis_terpilih = 1;
                elseif ($kiloan_id <= 6) $id_jenis_terpilih = 2;
                else $id_jenis_terpilih = 3;
            } else {
                if (isset($items_terpilih['satuan_9'])) $id_jenis_terpilih = 4;
                elseif (isset($items_terpilih['satuan_10'])) $id_jenis_terpilih = 5;
                // satuan dianggap Regular
            }

            if (!$id_jenis_terpilih) {
                $id_jenis_terpilih = 1;
            }

            // 2. Ambil estimasi hari dari tabel jenis_laundry
            $query_jenis = mysqli_query($conn, "SELECT estimasiHari FROM jenis_laundry WHERE idJenis = '$id_jenis_terpilih'");
            if ($row_jenis = mysqli_fetch_assoc($query_jenis)) {
                $estimasi_hari_str = $row_jenis['estimasiHari']; // misal "3 Hari"
                // Ambil angka dari string
                preg_match('/\d+/', $estimasi_hari_str, $matches);
                $estimasi_hari = isset($matches[0]) ? (int)$matches[0] : 1;
            } else {
                $estimasi_hari = 1; // default
            }

            // Jika Express dan estimasi > 1, kurangi 1 hari (minimal 1)
            if ($id_tipe == 1 && $estimasi_hari > 1) {
                $estimasi_hari -= 1;
            }

            // Hitung tanggal keluar
            $tanggal_keluar = date('Y-m-d', strtotime($tanggal_masuk . " + $estimasi_hari days"));

            // 3. Insert ke tabel laundry dengan Tanggal_Keluar
            $query_laundry = "INSERT INTO laundry (Id_Pelanggan, Id_Karyawan, Tanggal_Masuk, Tanggal_Keluar, Status, Total, Catatan) 
                              VALUES ('$id_pelanggan', NULL, '$tanggal_masuk', '$tanggal_keluar', '$status_awal', '$total_estimasi', '$catatan')";
            
            if (mysqli_query($conn, $query_laundry)) {
                $id_laundry_baru = mysqli_insert_id($conn);

                // Insert detail_laundry
                $query_detail = "INSERT INTO detail_laundry (Id_Laundry, idJenis, idTipe, jumlah, subtotal, status) 
                                 VALUES ('$id_laundry_baru', '$id_jenis_terpilih', '$id_tipe', 1, '$total_estimasi', 'Antri')";
                
                if (mysqli_query($conn, $query_detail)) {
                    unset($_SESSION['temporary_order']);
                    echo "<script>
                            alert('Pemesanan Berhasil Terkirim ke Sistem!');
                            window.location.href = 'views/auth/riwayat.php';
                          </script>";
                    exit();
                } else {
                    $error_message = "Gagal memproses rincian pesanan: " . mysqli_error($conn);
                }
            } else {
                $error_message = "Gagal mengirim pesanan ke server: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ILHAM Laundry - Aplikasi Pemesanan</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .alert-error {
            background-color: #FEE2E2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 12px;
            margin: 15px 24px 5px 24px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
    <script>
        function handleRadioClick(radio) {
            if (radio.dataset.wasChecked === "true") {
                radio.checked = false;
                radio.dataset.wasChecked = "false";
            } else {
                document.querySelectorAll(`input[name="${radio.name}"]`).forEach(el => el.dataset.wasChecked = "false");
                radio.dataset.wasChecked = "true";
            }
            radio.form.submit();
        }
    </script>
</head>
<body>

    <div class="desktop-wrapper">
        <form action="index.php" method="GET" class="mobile-app-container">
            
            <header class="app-header">
                <div class="brand-row">
                    <div class="logo-area">
                        <i class="fa-solid fa-soap logo-icon"></i>
                        <div class="logo-text">
                            <span class="main-brand">ILHAM</span>
                            <span class="sub-brand">L A U N D R Y</span>
                        </div>
                    </div>
                    
                    <?php if(isset($_SESSION['id_pelanggan'])): ?>
                        <div style="display: flex; gap: 8px;">
                            <a href="views/auth/riwayat.php" class="login-trigger-btn" style="background:#E0F2FE; color:#0066FF;" title="Riwayat Transaksi">
                                <i class="fa-solid fa-clock-history"></i>
                            </a>
                            <a href="views/auth/logout.php" class="login-trigger-btn" style="background:#FEE2E2; color:#EF4444;" title="Keluar" onclick="return confirm('Yakin ingin keluar?')">
                                <i class="fa-solid fa-power-off"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="views/auth/login.php" class="login-trigger-btn" title="Masuk Akun">
                            <i class="fa-solid fa-right-to-bracket"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="status-badge-wrapper">
                    <?php if ($status_toko === 'BUKA'): ?>
                        <span class="status-badge"><span class="dot"></span> BUKA / OPEN</span>
                    <?php else: ?>
                        <span class="status-badge status-closed"><span class="dot-closed"></span> TUTUP / CLOSED</span>
                    <?php endif; ?>
                </div>

                <div class="welcome-message">
                    <p>Selamat datang di,</p>
                    <h3>ILHAM LAUNDRY</h3>
                    <h2><?php echo isset($_SESSION['nama_pelanggan']) ? htmlspecialchars($_SESSION['nama_pelanggan']) : '👋'; ?></h2>
                </div>
            </header>

            <?php if (isset($error_message)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="search-section">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" placeholder="Cari layanan..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
            </div>

            <section class="category-section">
                <div class="section-header">
                    <h4><i class="fa-solid fa-filter filter-icon"></i> Filter Cepat</h4>
                    <span class="scroll-hint">Geser Pilih Layanan</span>
                </div>
                
                <div class="category-scroll">
                    <label class="category-item-label">
                        <input type="radio" name="filter" value="cuci-setrika" onchange="document.getElementById('fc').value='1'; this.form.submit()" <?php if($category_active == 'cuci-setrika') echo 'checked'; ?>>
                        <div class="category-design-box">
                            <div class="icon-wrapper"><i class="fa-solid fa-shirt"></i></div>
                            <span>Cuci Setrika</span>
                        </div>
                    </label>

                    <label class="category-item-label">
                        <input type="radio" name="filter" value="cuci-kering" onchange="document.getElementById('fc').value='1'; this.form.submit()" <?php if($category_active == 'cuci-kering') echo 'checked'; ?>>
                        <div class="category-design-box">
                            <div class="icon-wrapper"><i class="fa-solid fa-wind"></i></div>
                            <span>Cuci Kering</span>
                        </div>
                    </label>

                    <label class="category-item-label">
                        <input type="radio" name="filter" value="setrika" onchange="document.getElementById('fc').value='1'; this.form.submit()" <?php if($category_active == 'setrika') echo 'checked'; ?>>
                        <div class="category-design-box">
                            <div class="icon-wrapper"><i class="fa-solid fa-temperature-high"></i></div>
                            <span>Setrika</span>
                        </div>
                    </label>

                    <label class="category-item-label">
                        <input type="radio" name="filter" value="bedcover" onchange="document.getElementById('fc').value='1'; this.form.submit()" <?php if($category_active == 'bedcover') echo 'checked'; ?>>
                        <div class="category-design-box">
                            <div class="icon-wrapper"><i class="fa-solid fa-bed"></i></div>
                            <span>BedCover</span>
                        </div>
                    </label>

                    <label class="category-item-label">
                        <input type="radio" name="filter" value="sepatu" onchange="document.getElementById('fc').value='1'; this.form.submit()" <?php if($category_active == 'sepatu') echo 'checked'; ?>>
                        <div class="category-design-box">
                            <div class="icon-wrapper"><i class="fa-solid fa-shoe-prints"></i></div>
                            <span>Sepatu</span>
                        </div>
                    </label>
                </div>
                <input type="hidden" name="filter_changed" id="fc" value="">
            </section>

            <main class="services-list-section smooth-render-container">
                
                <div class="service-group-card">
                    <div class="group-header">
                        <div class="group-title-area">
                            <div class="group-icon-box"><i class="fa-solid fa-bag-shopping"></i></div>
                            <div>
                                <h5>Layanan Kiloan</h5>
                                <p>Cuci Setrika, Cuci Kering, Setrika (Per Kg)</p>
                            </div>
                        </div>
                    </div>

                    <div class="sub-services-container">
                        <?php 
                        $has_kiloan = false;
                        foreach($layanan_kiloan as $item): 
                            if($item['category'] !== $category_active) continue;
                            if(isset($_GET['search']) && $_GET['search'] != '' && strpos(strtolower($item['name']), strtolower($_GET['search'])) === false) continue;
                            
                            $has_kiloan = true;
                            $is_checked = (isset($selected_items['kiloan']) && $selected_items['kiloan'] == $item['id']);
                        ?>
                            <label class="sub-service-label">
                                <input type="radio" name="items[kiloan]" value="<?php echo $item['id']; ?>" onclick="handleRadioClick(this)" data-was-checked="<?php echo $is_checked ? 'true' : 'false'; ?>" <?php if($is_checked) echo 'checked'; ?>>
                                <div class="sub-service-item-design">
                                    <div class="sub-service-info">
                                        <h6><?php echo $item['name']; ?></h6>
                                        <span class="badge-tier <?php echo $item['badge_class']; ?>"><?php echo $item['badge']; ?></span>
                                    </div>
                                    <div class="price-and-check">
                                        <div class="sub-service-price">
                                            <span class="price-amount">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span><span class="price-unit">/Kg</span>
                                        </div>
                                        <div class="check-icon-wrapper"><i class="fa-solid fa-circle-check"></i></div>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                        
                        <?php if(!$has_kiloan): ?>
                            <p class="empty-notice">Tidak ada layanan kiloan di kategori ini.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="service-group-card mechanical-spacing">
                    <div class="group-header">
                        <div class="group-title-area">
                            <div class="group-icon-box item-satuan"><i class="fa-solid fa-socks"></i></div>
                            <div>
                                <h5>Layanan Satuan</h5>
                                <p>Bedcover, Selimut, Sepatu, Jas (Per Item)</p>
                            </div>
                        </div>
                    </div>

                    <div class="sub-services-container">
                        <?php 
                        $has_satuan = false;
                        foreach($layanan_satuan as $item): 
                            if($item['category'] !== $category_active) continue;
                            if(isset($_GET['search']) && $_GET['search'] != '' && strpos(strtolower($item['name']), strtolower($_GET['search'])) === false) continue;

                            $has_satuan = true;
                            $is_checked = isset($selected_items['satuan_' . $item['id']]);
                        ?>
                            <label class="sub-service-label">
                                <input type="checkbox" name="items[satuan_<?php echo $item['id']; ?>]" value="<?php echo $item['id']; ?>" onchange="this.form.submit()" <?php if($is_checked) echo 'checked'; ?>>
                                <div class="sub-service-item-design">
                                    <div class="sub-service-info">
                                        <h6><?php echo $item['name']; ?></h6>
                                        <span class="badge-tier"><?php echo $item['badge']; ?></span>
                                    </div>
                                    <div class="price-and-check">
                                        <div class="sub-service-price">
                                            <span class="price-amount">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span><span class="price-unit">/Pcs</span>
                                        </div>
                                        <div class="check-icon-wrapper"><i class="fa-solid fa-circle-check"></i></div>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>

                        <?php if(!$has_satuan): ?>
                            <p class="empty-notice">Tidak ada layanan satuan di kategori ini.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="notes-container-card">
                    <div class="notes-header">
                        <i class="fa-regular fa-comment-dots notes-icon"></i>
                        <label for="catatan_satuan">Catatan Tambahan Cucian <span style="color:red;">*</span></label>
                    </div>
                    <input type="text" id="catatan_satuan" name="catatan_satuan" class="input-notes-field" placeholder="Cth: Tas Biru / Jangan pakai parfum menyengat" value="<?php echo $catatan_satuan; ?>" required>
                </div>

                <div class="extra-options-section">
                    <h4 class="extra-section-title">Informasi & Bantuan</h4>
                    
                    <div class="extra-options-list">
                        <a href="views/auth/register.php" class="option-item-row">
                            <div class="option-left-content">
                                <div class="option-icon-frame font-blue"><i class="fa-solid fa-user-plus"></i></div>
                                <div class="option-text-info">
                                    <h6>Login / Daftar Akun</h6>
                                    <p>Masuk untuk melacak histori transaksi laundry</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right option-arrow"></i>
                        </a>

                        <a href="views/panduan/cara_order.php" class="option-item-row">
                            <div class="option-left-content">
                                <div class="option-icon-frame font-orange"><i class="fa-solid fa-circle-info"></i></div>
                                <div class="option-text-info">
                                    <h6>Cara Melakukan Pemesanan</h6>
                                    <p>Panduan langkah pick-up hingga pengantaran</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right option-arrow"></i>
                        </a>

                        <a href="https://wa.me/6283838367497" target="_blank" class="option-item-row">
                            <div class="option-left-content">
                                <div class="option-icon-frame font-green"><i class="fa-solid fa-headset"></i></div>
                                <div class="option-text-info">
                                    <h6>Chat CS (Ada Kendala?)</h6>
                                    <p>Hubungi layanan pelanggan kilat via WhatsApp</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right option-arrow"></i>
                        </a>
                    </div>
                </div>

                <input type="submit" style="display: none;">
            </main>

            <div class="sticky-order-bar">
                <div class="estimation-area">
                    <span class="est-label">Total Estimasi</span>
                    <span class="est-price">Rp <?php echo number_format($total_estimasi, 0, ',', '.'); ?></span>
                </div>
                <?php if ($status_toko === 'BUKA'): ?>
                    <button type="submit" name="tombol_order" class="order-submit-btn" style="border:none; cursor:pointer;">
                        <span>ORDER</span>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                <?php else: ?>
                    <button type="button" class="order-submit-btn btn-disabled" disabled style="border:none;">
                        <span>CLOSED</span>
                        <i class="fa-solid fa-lock"></i>
                    </button>
                <?php endif; ?>
            </div>

        </form>
    </div>

</body>
</html>