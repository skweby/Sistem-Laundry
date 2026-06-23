<?php
session_start();
require_once '../../config/database.php'; // Naik 2 tingkat ke root untuk mengambil config

$error = '';
$success = '';

if (isset($_POST['register'])) {
    // Ambil data dari form dan bersihkan (sanitize)
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $no_telp  = mysqli_real_escape_string($conn, trim($_POST['no_telp']));
    $alamat   = mysqli_real_escape_string($conn, trim($_POST['alamat']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    // PERBAIKAN: pastikan menggunakan variabel $password (tanpa kutip tunggal)
    if (empty($nama) || empty($no_telp) || empty($alamat) || empty($email) || empty($password)) {
        $error = "Semua kolom pendaftaran wajib diisi!";
    } else {
        // Cek apakah email sudah terdaftar
        $cek_email = mysqli_query($conn, "SELECT Email FROM Pelanggan WHERE Email = '$email'");
        
        if (mysqli_num_rows($cek_email) > 0) {
            $error = "Email ini sudah terdaftar! Silakan gunakan email lain.";
        } else {
            // Enkripsi password
            $password_aman = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO Pelanggan (Nama, NoTelp, Alamat, Email, Password) 
                      VALUES ('$nama', '$no_telp', '$alamat', '$email', '$password_aman')";

            if (mysqli_query($conn, $query)) {
                $success = "Pendaftaran berhasil! Silakan login menggunakan EMAIL Anda.";
            } else {
                $error = "Terjadi kesalahan sistem: " . mysqli_error($conn);
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
    <title>Daftar Akun - ILHAM Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F4F7FE; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .auth-card { background: #ffffff; width: 100%; max-width: 400px; padding: 32px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }
        .brand-header { text-align: center; margin-bottom: 24px; }
        .brand-header i { font-size: 32px; color: #0066FF; margin-bottom: 8px; }
        .brand-header h3 { font-size: 20px; color: #1A1A1A; font-weight: 700; }
        .alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
        .alert-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #4A4A4A; margin-bottom: 6px; }
        .input-box { width: 100%; padding: 12px 16px; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 14px; outline: none; transition: 0.2s; }
        .input-box:focus { border-color: #0066FF; box-shadow: 0 0 0 3px rgba(0,102,255,0.1); }
        .btn-submit { width: 100%; padding: 14px; background: #0066FF; color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; transition: 0.2s; margin-top: 8px; }
        .btn-submit:hover { background: #0052CC; }
        .switch-hint { text-align: center; margin-top: 20px; font-size: 13px; color: #718096; }
        .switch-hint a { color: #0066FF; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="brand-header">
        <i class="fa-solid fa-soap"></i>
        <h3>Buat Akun Baru</h3>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="input-box" placeholder="Masukkan nama Anda" required>
        </div>
        <div class="form-group">
            <label>Nomor Telepon</label>
            <input type="tel" name="no_telp" class="input-box" placeholder="Contoh: 08123456789" required>
        </div>
        <div class="form-group">
            <label>Alamat Rumah</label>
            <input type="text" name="alamat" class="input-box" placeholder="Masukkan alamat lengkap" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="input-box" placeholder="nama@email.com" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="input-box" placeholder="Buat password minimal 6 karakter" required>
        </div>
        <button type="submit" name="register" class="btn-submit">DAFTAR SEKARANG</button>
    </form>

    <p class="switch-hint">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    <p class="switch-hint" style="margin-top: 10px;"><a href="../../index.php" style="color: #718096;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a></p>
</div>

</body>
</html>