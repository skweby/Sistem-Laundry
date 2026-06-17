<?php
/**
 * Fungsi Kirim WhatsApp via Fonnte
 * 
 * @param string $nomor   Nomor tujuan (contoh: 628123456789)
 * @param string $pesan   Isi pesan yang akan dikirim
 * @return array          Response dari API
 */
function kirimWaFonnte($nomor, $pesan) {
    // Ganti dengan token device Anda dari dashboard Fonnte
    $token = "LsJmgZCJr7dB5tYYVmgZ";
    
    // Endpoint API Fonnte
    $url = "https://api.fonnte.com/send";
    
    // Data yang akan dikirim
    $data = [
        'target' => $nomor,
        'message' => $pesan,
        'countryCode' => '62' // Kode negara Indonesia
    ];
    
    // Inisialisasi CURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $token // Token dari dashboard Fonnte
        ],
    ]);
    
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    
    if ($error) {
        return ['status' => 'error', 'message' => $error];
    }
    
    return json_decode($response, true);
}
?>