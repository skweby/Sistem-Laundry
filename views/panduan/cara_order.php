<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cara Pemesanan - ILHAM Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F4F7FE; display: flex; justify-content: center; }

        .desktop-wrapper {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            background: #ffffff;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            position: relative;
            padding-bottom: 32px;
        }

        /* ===== HEADER ===== */
        .app-header-mini {
            background: linear-gradient(135deg, #0066FF, #0052CC);
            color: white;
            padding: 24px 20px 28px;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            gap: 6px;
            align-items: center;
            background: rgba(255,255,255,0.18);
            padding: 7px 14px;
            border-radius: 20px;
            transition: background 0.2s;
        }
        .back-btn:hover { background: rgba(255,255,255,0.28); }
        .header-badge {
            font-size: 11px;
            font-weight: 700;
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }
        .header-title-area { }
        .header-title-area h2 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .header-title-area p {
            font-size: 12px;
            opacity: 0.85;
            font-weight: 500;
        }

        /* ===== MAIN CONTENT ===== */
        .page-content { padding: 20px; }

        /* ===== INFO BANNER ===== */
        .info-banner {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .info-banner i { color: #0066FF; font-size: 18px; margin-top: 2px; flex-shrink: 0; }
        .info-banner p { font-size: 12.5px; color: #1E40AF; line-height: 1.6; font-weight: 500; }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: #1A1A1A;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: #F1F5F9;
            border-radius: 2px;
        }

        /* ===== STEP CARDS ===== */
        .step-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }

        .step-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            position: relative;
            transition: box-shadow 0.2s;
        }
        .step-card:hover { box-shadow: 0 4px 16px rgba(0,102,255,0.08); }

        .step-number {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: #0066FF;
            color: white;
            font-weight: 800;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .step-number.orange { background: #F97316; }
        .step-number.green  { background: #10B981; }
        .step-number.purple { background: #8B5CF6; }
        .step-number.teal   { background: #06B6D4; }

        .step-body h5 {
            font-size: 14px;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 5px;
        }
        .step-body p {
            font-size: 12.5px;
            color: #64748B;
            line-height: 1.6;
        }
        .step-body .step-tag {
            display: inline-block;
            margin-top: 8px;
            background: #E0F2FE;
            color: #0369A1;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
        }
        .step-body .step-tag.orange { background: #FEF3C7; color: #D97706; }
        .step-body .step-tag.green  { background: #DCFCE7; color: #166534; }

        /* ===== LAYANAN CARDS ===== */
        .layanan-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 28px; }

        .layanan-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            transition: box-shadow 0.2s;
        }
        .layanan-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.07); }
        .layanan-card .icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto 10px;
        }
        .bg-blue   { background: #EFF6FF; color: #0066FF; }
        .bg-cyan   { background: #ECFEFF; color: #06B6D4; }
        .bg-orange { background: #FFF7ED; color: #F97316; }
        .bg-purple { background: #F5F3FF; color: #8B5CF6; }
        .bg-green  { background: #F0FDF4; color: #10B981; }

        .layanan-card h6 { font-size: 12.5px; font-weight: 700; color: #1A1A1A; margin-bottom: 3px; }
        .layanan-card p  { font-size: 11px; color: #64748B; }
        .layanan-card .harga {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 800;
            color: #0066FF;
        }

        /* ===== FAQ ===== */
        .faq-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }

        .faq-item {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            overflow: hidden;
        }
        .faq-question {
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 700;
            color: #1A1A1A;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        .faq-question i { color: #0066FF; font-size: 12px; transition: transform 0.3s; }
        .faq-answer {
            padding: 0 16px;
            font-size: 12.5px;
            color: #64748B;
            line-height: 1.7;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease, padding 0.25s;
        }
        .faq-item.open .faq-answer {
            max-height: 200px;
            padding: 0 16px 14px;
        }
        .faq-item.open .faq-question i { transform: rotate(180deg); }

        /* ===== CTA BOTTOM ===== */
        .cta-area { display: flex; flex-direction: column; gap: 10px; }

        .cta-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border-radius: 14px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: opacity 0.2s;
        }
        .cta-btn:hover { opacity: 0.88; }
        .cta-primary   { background: #0066FF; color: white; }
        .cta-whatsapp  { background: #22C55E; color: white; }
        .cta-secondary {
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #E2E8F0;
        }
    </style>
</head>
<body>

<div class="desktop-wrapper">

    <!-- HEADER -->
    <header class="app-header-mini">
        <div class="header-top">
            <a href="../../index.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> Beranda
            </a>
            <span class="header-badge">📖 PANDUAN</span>
        </div>
        <div class="header-title-area">
            <h2>Cara Melakukan Pemesanan</h2>
            <p>Panduan lengkap pick-up, proses, hingga pengantaran laundry</p>
        </div>
    </header>

    <div class="page-content">

        <!-- INFO BANNER -->
        <div class="info-banner">
            <i class="fa-solid fa-circle-info"></i>
            <p>Pemesanan dilakukan secara online di aplikasi ini. Pakaian akan <strong>dijemput</strong> oleh kurir kami setelah pesanan dikonfirmasi. Tidak perlu antre ke toko!</p>
        </div>

        <!-- LANGKAH-LANGKAH -->
        <p class="section-title"><i class="fa-solid fa-list-ol" style="color:#0066FF;"></i> Langkah Pemesanan</p>

        <div class="step-list">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-body">
                    <h5>Pilih Layanan Laundry</h5>
                    <p>Di halaman utama, pilih kategori layanan (Cuci Setrika, Cuci Kering, atau Setrika). Klik layanan kiloan atau satuan sesuai kebutuhan cucian Anda.</p>
                    <span class="step-tag">Kiloan / Satuan tersedia</span>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number orange">2</div>
                <div class="step-body">
                    <h5>Isi Catatan Tambahan</h5>
                    <p>Tulis keterangan khusus untuk cucian Anda, misalnya warna tas, bahan sensitif, atau permintaan tanpa parfum. Kolom ini <strong>wajib diisi</strong>.</p>
                    <span class="step-tag orange">Wajib diisi</span>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number" style="background:#10B981;">3</div>
                <div class="step-body">
                    <h5>Klik Tombol ORDER</h5>
                    <p>Tekan tombol <strong>ORDER</strong> di bagian bawah layar. Jika belum login, Anda akan diarahkan ke halaman login terlebih dahulu. Data pesanan tidak akan hilang.</p>
                    <span class="step-tag green">Login otomatis disimpan</span>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number purple">4</div>
                <div class="step-body">
                    <h5>Login / Daftar Akun</h5>
                    <p>Masuk menggunakan nama pengguna Anda atau daftar akun baru jika belum memiliki. Pendaftaran hanya memerlukan nama, nomor telepon, alamat, dan email.</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number teal">5</div>
                <div class="step-body">
                    <h5>Pesanan Masuk & Penjemputan</h5>
                    <p>Pesanan Anda masuk ke sistem dengan status <strong>Baru</strong>. Tim kami akan segera menghubungi untuk konfirmasi jadwal penjemputan cucian ke lokasi Anda.</p>
                    <span class="step-tag">Kurir akan menjemput</span>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number" style="background:#F97316;">6</div>
                <div class="step-body">
                    <h5>Proses & Pengantaran</h5>
                    <p>Cucian diproses sesuai layanan yang dipilih. Status akan berubah menjadi <strong>Proses</strong>, dan pakaian bersih diantarkan kembali ke alamat Anda sesuai estimasi waktu.</p>
                    <span class="step-tag orange">Pantau via Riwayat Order</span>
                </div>
            </div>
        </div>

        <!-- DAFTAR LAYANAN -->
        <p class="section-title"><i class="fa-solid fa-tags" style="color:#F97316;"></i> Daftar Layanan</p>

        <div class="layanan-grid">
            <div class="layanan-card">
                <div class="icon-wrap bg-blue"><i class="fa-solid fa-shirt"></i></div>
                <h6>Cuci Setrika</h6>
                <p>1 – 4 Hari Kerja</p>
                <div class="harga">Rp 4.500 – 7.500/Kg</div>
            </div>
            <div class="layanan-card">
                <div class="icon-wrap bg-cyan"><i class="fa-solid fa-wind"></i></div>
                <h6>Cuci Kering</h6>
                <p>1 – 2 Hari Kerja</p>
                <div class="harga">Rp 6.000 – 7.000/Kg</div>
            </div>
            <div class="layanan-card">
                <div class="icon-wrap bg-orange"><i class="fa-solid fa-fire"></i></div>
                <h6>Setrika Saja</h6>
                <p>1 – 2 Hari Kerja</p>
                <div class="harga">Rp 4.000 – 5.000/Kg</div>
            </div>
            <div class="layanan-card">
                <div class="icon-wrap bg-purple"><i class="fa-solid fa-bed"></i></div>
                <h6>Bedcover</h6>
                <p>Per Item / Pcs</p>
                <div class="harga">Rp 35.000/Pcs</div>
            </div>
            <div class="layanan-card" style="grid-column: 1 / -1;">
                <div class="icon-wrap bg-green" style="margin:0 auto 10px;"><i class="fa-solid fa-shoe-prints"></i></div>
                <h6>Cuci Sepatu Sneaker</h6>
                <p>Deep Clean – Per Pasang</p>
                <div class="harga">Rp 25.000/Pcs</div>
            </div>
        </div>

        <!-- FAQ -->
        <p class="section-title"><i class="fa-solid fa-circle-question" style="color:#8B5CF6;"></i> Pertanyaan Umum</p>

        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Apakah ada biaya tambahan untuk penjemputan?
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Untuk area tertentu, penjemputan dan pengantaran <strong>gratis</strong>. Jika lokasi Anda berada lebih dari 3km dari toko, tim kami akan menginformasikan biaya ongkir saat konfirmasi via WhatsApp.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Bagaimana cara memantau status cucian saya?
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Login ke akun Anda, lalu masuk ke menu <strong>Riwayat Order</strong> di pojok kanan atas sebelah tombol logout. Status akan diperbarui secara real-time oleh admin: Baru → Proses → Selesai.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Berapa lama estimasi pengerjaan laundry?
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Tergantung <stong>layanan</stong> yang dipilih. Estimasi waktu pengerjaan dapat dilihat di sebelah layanan yang anda pilih. Estimasi dihitung sejak cucian diterima di toko.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Apakah bisa pesan tanpa buat akun?
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Anda bisa memilih layanan dan mengisi catatan tanpa akun. Namun, <strong>akun diperlukan</strong> saat menekan tombol ORDER agar pesanan tersimpan dan status bisa dilacak.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Metode pembayaran apa yang diterima?
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Saat ini tersedia pembayaran <strong>non-tunai (QRIS)</strong> setelah laundry anda di proses dan laundry tidak akan dikirim sebelum anda menyelesaikan pembayaran.
                </div>
            </div>
        </div>

        <!-- TOMBOL CTA -->
        <div class="cta-area">
            <a href="../../index.php" class="cta-btn cta-primary">
                <i class="fa-solid fa-bag-shopping"></i> Mulai Pesan Sekarang
            </a>
            <a href="https://wa.me/6283838367497" target="_blank" class="cta-btn cta-whatsapp">
                <i class="fa-brands fa-whatsapp"></i> Hubungi CS via WhatsApp
            </a>
            <a href="../../views/auth/register.php" class="cta-btn cta-secondary">
                <i class="fa-solid fa-user-plus"></i> Belum punya akun? Daftar di sini
            </a>
        </div>

    </div><!-- /page-content -->
</div><!-- /desktop-wrapper -->

<script>
    function toggleFaq(el) {
        const item = el.closest('.faq-item');
        const isOpen = item.classList.contains('open');
        // Tutup semua
        document.querySelectorAll('.faq-item').forEach(f => f.classList.remove('open'));
        // Buka yang diklik (jika sebelumnya tertutup)
        if (!isOpen) item.classList.add('open');
    }
</script>

</body>
</html>