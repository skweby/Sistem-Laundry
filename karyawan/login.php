<?php
session_start();
require_once '../config/database.php';

$error = '';

if (isset($_POST['login_karyawan'])) {
    // Perbaiki: gunakan 'Username' bukan 'username_karyawan'
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // Perbaiki: kolom yang benar adalah 'Username'
        $query = "SELECT * FROM Karyawan WHERE Username = '$username'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (password_verify($password, $row['Password']) || $password === $row['Password']) {
                $_SESSION['karyawan_logged'] = true;
                $_SESSION['id_karyawan']     = $row['IdKaryawan'];
                $_SESSION['nama_karyawan']   = $row['Nama'];
                $_SESSION['role']            = 'karyawan';
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username Karyawan tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Karyawan - ILHAM LAUNDRY</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #F4F7FE; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-box { background: white; width: 100%; max-width: 380px; padding: 32px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 24px; }
        .header i { font-size: 36px; color: #8B5CF6; }
        .header h2 { font-size: 20px; margin-top: 10px; color: #1A1A1A; }
        .header p { font-size: 12px; color: #94A3B8; margin-top: 4px; }
        .alert { background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; border: 1px solid #FCA5A5; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #4A4A4A; }
        .input-control { width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px; font-size: 14px; outline: none; }
        .input-control:focus { border-color: #8B5CF6; }
        .btn-login { width: 100%; padding: 12px; background: #8B5CF6; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 14px; }
        .btn-login:hover { background: #7C3AED; }
        .back-link { display: block; text-align: center; margin-top: 16px; font-size: 13px; color: #64748B; text-decoration: none; }
        .back-link:hover { color: #8B5CF6; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="header">
        <i class="fa-solid fa-user-tie"></i>
        <h2>Login Karyawan</h2>
        <p>Portal Karyawan Laundry</p>
    </div>
    <?php if($error): ?>
        <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="input-control" placeholder="Masukkan username karyawan" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="input-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login_karyawan" class="btn-login">MASUK DASHBOARD</button>
    </form>
    <a href="../index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
</div>
</body>
</html>