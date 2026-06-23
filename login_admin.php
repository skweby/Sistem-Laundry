<?php
session_start();
require_once 'config/database.php';

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // 1. CEK ADMIN
        $q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE Username = '$username'");
        if (mysqli_num_rows($q_admin) === 1) {
            $row = mysqli_fetch_assoc($q_admin);
            if ($password === $row['password'] || password_verify($password, $row['password'])) {
                $_SESSION['user_logged'] = true;
                $_SESSION['id_user'] = $row['idAdmin'];
                $_SESSION['nama_user'] = $row['nama'];
                $_SESSION['role'] = 'admin';
                header("Location: admin/index.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            // 2. CEK KARYAWAN
            $q_karyawan = mysqli_query($conn, "SELECT * FROM karyawan WHERE username = '$username'");
            if (mysqli_num_rows($q_karyawan) === 1) {
                $row = mysqli_fetch_assoc($q_karyawan);
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_logged'] = true;
                    $_SESSION['id_user'] = $row['Id_Karyawan'];
                    $_SESSION['nama_user'] = $row['Nama_Karyawan'];
                    $_SESSION['role'] = $row['role'];
                    header("Location: admin/index.php");
                    exit();
                } else {
                    $error = "Password salah!";
                }
            } else {
                $error = "Username tidak ditemukan!";
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
    <title>Login Admin / Karyawan - ILHAM LAUNDRY</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #F4F7FE; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-box { background: white; width: 100%; max-width: 380px; padding: 32px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 24px; }
        .header i { font-size: 36px; color: #0066FF; }
        .header h2 { font-size: 20px; margin-top: 10px; color: #1A1A1A; }
        .alert { background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; border: 1px solid #FCA5A5; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #4A4A4A; }
        .input-control { width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px; font-size: 14px; outline: none; }
        .input-control:focus { border-color: #0066FF; }
        .btn-login { width: 100%; padding: 12px; background: #0066FF; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 14px; }
        .btn-login:hover { background: #0052CC; }
        .back-link { display: block; text-align: center; margin-top: 16px; color: #64748B; text-decoration: none; font-size: 13px; }
        .back-link:hover { color: #0066FF; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="header">
        <i class="fa-solid fa-user-shield"></i>
        <h2>Portal Admin / Karyawan</h2>
        <p style="font-size:12px; color:#64748B;">Login untuk mengelola laundry</p>
    </div>
    <?php if($error): ?>
        <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="input-control" placeholder="Masukkan username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="input-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn-login">MASUK</button>
    </form>
    <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
</div>
</body>
</html>