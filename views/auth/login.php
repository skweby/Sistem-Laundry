<?php
session_start();
require_once '../../config/database.php'; // Sesuaikan jenjang folder ke database.php

$error = '';

if (isset($_POST['login_proses'])) {
    $nama_input = mysqli_real_escape_string($conn, trim($_POST['nama_user']));
    $password_input = trim($_POST['password_user']);

    if (empty($nama_input) || empty($password_input)) {
        $error = "Semua kolom input login wajib diisi!";
    } else {
        $q_pelanggan = mysqli_query($conn, "SELECT * FROM Pelanggan WHERE Nama = '$nama_input'");
        if (mysqli_num_rows($q_pelanggan) === 1) {
            $r_pelanggan = mysqli_fetch_assoc($q_pelanggan);
            
            // Mendukung password teks biasa maupun yang di-hash dengan password_verify
            if ($password_input === $r_pelanggan['Password'] || password_verify($password_input, $r_pelanggan['Password'])) {
                $_SESSION['id_pelanggan']   = $r_pelanggan['IdPelanggan'];
                $_SESSION['nama_pelanggan'] = $r_pelanggan['Nama'];

                // JIKA USER BARU SAJA MENCOBA ORDER SEBELUM LOGIN (Paling Krusial):
                if (isset($_SESSION['temporary_order'])) {
                    $order_params = $_SESSION['temporary_order'];
                    unset($_SESSION['temporary_order']); // Bersihkan temporary agar tidak loop
                    
                    // Bangun ulang string parameter URL dan tambahkan tombol_order=1 secara otomatis
                    $redirect_url = "../../index.php?" . http_build_query($order_params);
                    if (strpos($redirect_url, 'tombol_order=') === false) {
                        $redirect_url .= "&tombol_order=1";
                    }
                    
                    // Kembalikan ke index.php agar di-intercept oleh status sudah login, lalu auto-redirect ke riwayat.php
                    header("Location: " . $redirect_url);
                } else {
                    // JIKA LOGIN BIASA TANPA MEMBAWA PESANAN TERTUNDA: Langsung ke Riwayat Pelanggan
                    header("Location: riwayat.php");
                }
                exit();
            } else {
                $error = "Password Akun Salah!";
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
    <title>Login Sistem - RIFFANASH LAUNDRY</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans', sans-serif; }
        body { background:#F4F7FE; display:flex; justify-content:center; align-items:center; min-height:100vh; }
        .login-card { background:white; width:100%; max-width:380px; padding:32px; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.05); }
        .logo-header { text-align:center; margin-bottom:24px; color:#0066FF; }
        .logo-header h3 { color:#1A1A1A; margin-top:8px; font-size:18px; }
        .error-lbl { background:#FEE2E2; color:#991B1B; padding:10px; border-radius:8px; font-size:12px; margin-bottom:15px; text-align:center; font-weight:600; }
        .form-box { margin-bottom:15px; }
        .form-box label { display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:#4A4A4A; }
        .input-input { width:100%; padding:12px; border:1px solid #E2E8F0; border-radius:10px; outline:none; }
        .btn-submit { width:100%; padding:12px; background:#0066FF; color:white; border:none; border-radius:10px; font-weight:700; cursor:pointer; }
        .hint-reg { text-align: center; font-size: 13px; margin-top: 15px; color: #718096; }
        .hint-reg a { color: #0066FF; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="logo-header">
        <i class="fa-solid fa-soap" style="font-size:32px;"></i>
        <h3>Masuk ke Riffanash Laundry</h3>
        <p style="font-size:11px; color:gray; margin-top: 4px;">Gunakan Nama Pelanggan</p>
    </div>

    <?php if($error): ?><div class="error-lbl"><?php echo $error; ?></div><?php endif; ?>

    <form action="" method="POST">
        <div class="form-box">
            <label>Nama Pengguna</label>
            <input type="text" name="nama_user" class="input-input" placeholder="Masukkan nama Anda" required>
        </div>
        <div class="form-box">
            <label>Password</label>
            <input type="password" name="password_user" class="input-input" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login_proses" class="btn-submit">MASUK KE SISTEM</button>
    </form>
    
    <p class="hint-reg">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>
</body>
</html>