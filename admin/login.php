<?php
session_start();
require_once '../config/database.php';

$error = '';

if (isset($_POST['login_admin'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // Query disesuaikan 100% dengan Class Diagram Admin (idAdmin, nama, Username, password)
        $query = "SELECT * FROM Admin WHERE Username = '$username'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Menggunakan password_verify atau plain-text sesuai data awal dummy kamu. 
            // Disarankan menggunakan password_verify demi keamanan akademik.
            if (password_verify($password, $row['password']) || $password === $row['password']) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['id_admin']     = $row['idAdmin'];
                $_SESSION['nama_admin']   = $row['nama'];
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username Admin tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - RIFFANASH LAUNDRY</title>
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
    </style>
</head>
<body>
<div class="login-box">
    <div class="header">
        <i class="fa-solid fa-user-shield"></i>
        <h2>Portal Admin Laundry</h2>
    </div>
    <?php if($error): ?>
        <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="input-control" placeholder="Masukkan username admin" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="input-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login_admin" class="btn-login">MASUK DASHBOARD</button>
    </form>
</div>
</body>
</html>