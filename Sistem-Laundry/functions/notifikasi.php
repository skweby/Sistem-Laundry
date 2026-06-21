<?php
/**
 * Membuat isi pesan chat sistem berdasarkan status laundry terbaru.
 * Pesan ini akan tampil sebagai "chat" otomatis dari sistem ke pelanggan
 * di halaman views/auth/chat.php
 *
 * @param string $nama       Nama pelanggan
 * @param int    $id_laundry ID order laundry
 * @param string $status     Status terbaru
 * @param float  $total      Total biaya (opsional, dipakai pada status tertentu)
 * @return string            Isi pesan chat
 */
function buatPesanChatStatus($nama, $id_laundry, $status, $total = null) {
    switch ($status) {
        case 'Baru':
        case 'Diterima':
            return "Halo $nama, pesanan laundry #$id_laundry telah kami terima dan akan segera kami proses. 🧺";

        case 'Pickup':
            return "Kurir kami sedang menjemput pesanan laundry #$id_laundry. Mohon siapkan cucian Anda di lokasi. 🚚";

        case 'Proses Cuci':
            return "Pesanan laundry #$id_laundry sedang dalam proses pencucian. Kami akan kabari setelah selesai. 🧼";

        case 'Proses Setrika':
            return "Pesanan laundry #$id_laundry sedang dalam proses penyetrikaan. Tunggu sebentar lagi ya! 👕";

        case 'Selesai Proses':
            $info_total = ($total !== null && $total > 0)
                ? " Total biaya: Rp " . number_format($total, 0, ',', '.') . "."
                : '';
            return "Pesanan laundry #$id_laundry telah SELESAI DIPROSES.$info_total Silakan lakukan pembayaran dan konfirmasi untuk pengantaran/pengambilan. ✅";

        case 'Diantar':
            return "Pesanan laundry #$id_laundry sedang dalam perjalanan menuju alamat Anda. 🚗";

        case 'Selesai':
            return "Pesanan laundry #$id_laundry telah SELESAI. Cucian Anda sudah bisa diambil/diterima. Terima kasih telah menggunakan layanan kami! 🎉";

        default:
            return "Status pesanan laundry #$id_laundry telah diperbarui menjadi: \"$status\".";
    }
}

/**
 * Menyimpan pesan chat sistem ke database (tabel chat_status) agar
 * muncul di halaman riwayat chat pelanggan setiap kali status order berubah.
 *
 * @param mysqli $conn
 * @param int    $id_laundry
 * @param int    $id_pelanggan
 * @param string $status_baru
 * @param float  $total
 */
function simpanChatStatus($conn, $id_laundry, $id_pelanggan, $status_baru, $total = null) {
    $id_laundry   = (int)$id_laundry;
    $id_pelanggan = (int)$id_pelanggan;

    if ($id_laundry <= 0 || $id_pelanggan <= 0) {
        return false;
    }

    $nama = '';
    $q_nama = mysqli_query($conn, "SELECT Nama FROM Pelanggan WHERE IdPelanggan = $id_pelanggan");
    if ($q_nama && $r_nama = mysqli_fetch_assoc($q_nama)) {
        $nama = $r_nama['Nama'];
    }

    $pesan = buatPesanChatStatus($nama, $id_laundry, $status_baru, $total);

    $pesan_aman  = mysqli_real_escape_string($conn, $pesan);
    $status_aman = mysqli_real_escape_string($conn, $status_baru);

    return mysqli_query($conn, "
        INSERT INTO chat_status (id_laundry, id_pelanggan, status, pesan)
        VALUES ($id_laundry, $id_pelanggan, '$status_aman', '$pesan_aman')
    ");
}
?>
