<?php
session_start();
require_once '../config/database.php';

// Proteksi halaman: Pastikan user sudah login
if (!isset($_SESSION['user_logged'])) {
    header("Location: login.php");
    exit();
}

// Validasi parameter ID yang dikirim lewat URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<h3 style='text-align:center;font-family:sans-serif;'>ID Order tidak ditemukan.</h3>");
}

$id_laundry = (int)$_GET['id'];

// Query join untuk mengambil data transaksi Laundry sekaligus data Pelanggannya
$query = mysqli_query($conn, "
    SELECT L.*, P.Nama, P.Alamat, P.NoTelp
    FROM laundry L
    JOIN pelanggan P ON L.Id_Pelanggan = P.IdPelanggan
    WHERE L.Id_Laundry = '$id_laundry'
");

$data = mysqli_fetch_assoc($query);

// Jika ID tidak ada di database
if (!$data) {
    die("<h3 style='text-align:center;font-family:sans-serif;'>Data transaksi tidak ditemukan.</h3>");
}

// Logika fallback untuk menentukan Nama Layanan berdasarkan harga per KG (sesuai struktur manajemen_order.php)
$kg_tersimpan = (float)$data['Berat_Kg'];
$total_tersimpan = (float)$data['Total'];
$hpk_default = ($kg_tersimpan > 0 && $total_tersimpan > 0) ? round($total_tersimpan / $kg_tersimpan) : 6500;

$nama_servis = "Cuci Laundry";
if ($hpk_default == 7500) $nama_servis = "Cuci Setrika – 1 Hari";
elseif ($hpk_default == 6500) $nama_servis = "Cuci Setrika – 2 Hari";
elseif ($hpk_default == 5500) $nama_servis = "Cuci Setrika – 3 Hari";
elseif ($hpk_default == 4500) $nama_servis = "Cuci Setrika – 4 Hari";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota_#<?php echo $data['Id_Laundry']; ?></title>
    <style>
        /* Pengaturan global font struk belanja mini */
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 210px; /* Standar lebar area cetak printer thermal 58mm */
            margin: 0;
            padding: 5px;
            font-size: 11px;
            color: #000;
            background-color: #fff;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .brand-name { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
        .brand-sub { font-size: 10px; margin-bottom: 4px; line-height: 1.2; }
        
        .divider { 
            border-top: 1px dashed #000; 
            margin: 6px 0; 
        }
        
        /* Layout kolom baris informasi menggunakan flexbox murni */
        .nota-row { 
            display: flex; 
            justify-content: space-between; 
            margin: 2px 0; 
        }
        .nota-row span:first-child {
            max-width: 90px;
            word-wrap: break-word;
        }
        .nota-row span:last-child { 
            font-weight: bold; 
            text-align: right;
            max-width: 120px;
            word-wrap: break-word;
        }
        
        .total-row { 
            font-size: 12px; 
            font-weight: bold; 
            margin-top: 6px; 
        }

        /* Aturan khusus saat dokumen dikirim ke mesin printer */
        @media print {
            body { 
                width: 100%; 
                max-width: 58mm; 
                margin: 0; 
                padding: 0;
            }
            /* Menghilangkan margin/header otomatis bawaan browser internet */
            @page { 
                margin: 0; 
            } 
        }
    </style>
</head>
<body onload="window.print();">

    <div class="text-center">
        <div class="brand-name">ILHAM LAUNDRY</div>
        <div class="brand-sub">Jl. Petoran, Solo</div>
        <div class="brand-sub">Telp: 083838367497</div>
        <div class="brand-sub" style="margin-top: 5px; font-weight: bold;">INVOICE #<?php echo $data['Id_Laundry']; ?></div>
    </div>

    <div class="divider"></div>

    <div class="nota-row"><span>Pelanggan:</span><span><?php echo htmlspecialchars($data['Nama']); ?></span></div>
    <div class="nota-row"><span>No. HP:</span><span><?php echo htmlspecialchars($data['NoTelp']); ?></span></div>
    <div class="nota-row"><span>Masuk:</span><span><?php echo date('d/m/Y', strtotime($data['Tanggal_Masuk'])); ?></span></div>
    <div class="nota-row"><span>Keluar:</span><span><?php echo $data['Tanggal_Keluar'] ? date('d/m/Y', strtotime($data['Tanggal_Keluar'])) : '-'; ?></span></div>
    <div class="nota-row"><span>Status:</span><span><?php echo htmlspecialchars($data['Status']); ?></span></div>

    <div class="divider"></div>

    <div class="nota-row"><span>Layanan:</span><span><?php echo htmlspecialchars($nama_servis); ?></span></div>
    <div class="nota-row"><span>Berat:</span><span><?php echo $kg_tersimpan > 0 ? number_format($kg_tersimpan, 1, ',', '.') . ' Kg' : '-'; ?></span></div>
    <div class="nota-row"><span>Harga/Kg:</span><span>Rp <?php echo number_format($hpk_default, 0, ',', '.'); ?></span></div>
    <div class="nota-row"><span>Catatan:</span><span><?php echo htmlspecialchars($data['Catatan'] ?: '-'); ?></span></div>

    <div class="divider"></div>

    <div class="nota-row total-row"><span>TOTAL:</span><span>Rp <?php echo number_format($total_tersimpan, 0, ',', '.'); ?></span></div>

    <div class="divider"></div>

    <div class="text-center brand-sub" style="margin-top: 8px;">
        Terima kasih atas kepercayaan Anda 🙏<br>
        Layanan Laundry Bersih & Rapih.
    </div>

</body>
</html>