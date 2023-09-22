<?php

$apiKey = "6c90cc84ec280d7a0c59e5da846ada94";

$merchantCode = "DS16784";

$duitku_url2 = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';
$hash = md5($merchantCode . $_GET['id_transaksi'] .$_GET['subtotal'] . $apiKey);

$data2 = array(
    'merchantCode' => $merchantCode,
    'paymentAmount' => $_GET['subtotal'],
    'paymentMethod' => $_GET['paymentMethod'],
    'merchantOrderId' => strval($_GET['id_transaksi']),
    'productDetails' => $GLOBALS['nama_barang'],
    'customerVaName' => 'Tegar',
    'email' => 'tegarrizky237@email.com',
    'phoneNumber' => '0895322728059',
    'callbackUrl' => 'https://github.com/TgrRys',
    'returnUrl' => 'https://github.com/TgrRys',
    'signature' => $hash,
);


$data_json2 = json_encode($data2);

// Inisialisasi curl untuk URL kedua
$ch2 = curl_init();

// Set konfigurasi curl untuk URL kedua
curl_setopt($ch2, CURLOPT_URL, $duitku_url2);
curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch2, CURLOPT_POSTFIELDS, $data_json2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_json2),
    'Authorization: ' . $apiKey, // Tambahkan header otorisasi untuk URL kedua
));
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);

// Eksekusi permintaan ke URL kedua
$response2 = curl_exec($ch2);

// Tutup curl untuk URL kedua
curl_close($ch2);

// Proses respons dari API Duitku untuk URL kedua
$result2 = json_decode($response2, true);


if ($response2) {
    // Decode respons JSON dari API Duitku
    $result2 = json_decode($response2, true);
    var_dump($result2);

    // Periksa apakah permintaan berhasil
    // Ambil URL pembayaran pertama dari array
    $paymentUrl = $result2['paymentUrl'];
        
    // Arahkan pengguna ke URL pembayaran dan memuat ulang halaman
    header("Location: " . $paymentUrl);
    exit();
} else {
    echo "Gagal melakukan permintaan ke API Duitku.";
}
