<?php
require_once 'functions/whatsapp.php';

// Ganti dengan nomor HP Anda sendiri untuk test
$nomor_test = "6289601585136"; 
$pesan_test = "✅ Test notifikasi dari ILHAM LAUNDRY\n\nJika Anda menerima pesan ini, berarti integrasi Fonnte berhasil!";

$result = kirimWaFonnte($nomor_test, $pesan_test);

echo "<pre>";
print_r($result);
echo "</pre>";
?>