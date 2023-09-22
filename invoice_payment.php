<?php
include("koneksi.php");

// Ambil ID transaksi dari URL
$id_transaksi = $_GET["id_transaksi"];

// Query untuk mengambil data transaksi
$sql_transaksi = "SELECT transaksi.id_transaksi, barang.nama_barang, transaksi.jumlah, barang.harga
                  FROM transaksi
                  JOIN barang ON transaksi.id_barang = barang.id_barang
                  WHERE transaksi.id_transaksi = $id_transaksi";

$result_transaksi = mysqli_query($conn, $sql_transaksi);

if (!$result_transaksi) {
    die("Error in SQL query: " . mysqli_error($conn));
}

// Menghitung subtotal berdasarkan data transaksi
$subtotal = 0;

if (mysqli_num_rows($result_transaksi) > 0) {
    echo "<h2>Detail Transaksi</h2>";
    echo "<p>ID Transaksi: $id_transaksi</p>";

    echo "<div class='card'>";
    echo "<div class='card-content'>";
    while ($row_transaksi = mysqli_fetch_assoc($result_transaksi)) {
        $nama_barang = $row_transaksi["nama_barang"];
        $jumlah = $row_transaksi["jumlah"];
        $harga = $row_transaksi["harga"];
        $total_harga = $harga * $jumlah;

        // Akumulasikan subtotal
        $subtotal += $total_harga;

        echo "<h3>Nama Barang:</h3>";
        echo "<p>$nama_barang</p>";

        echo "<h3>Jumlah:</h3>";
        echo "<p>$jumlah</p>";

        echo "<h3>Harga:</h3>";
        echo "<p>Rp " . number_format($harga, 0, ",", ".") . "</p>";
    }

    // Tampilkan total pembayaran
    echo "<h3>Total Pembayaran:</h3>";
    echo "<p>Rp " . number_format($subtotal, 0, ",", ".") . "</p>";
    echo "</div>"; // Tutup card-content
    echo "</div>"; // Tutup card

    if (isset($_GET["metode_pembayaran"])) {
        $metode_pembayaran = $_GET["metode_pembayaran"];

        // Mendapatkan daftar metode pembayaran dari API Duitku
        if ($metode_pembayaran === "duitku") {
            // Set kode merchant Anda
            $merchantCode = "DS16784";
            // Set merchant key Anda
            $apiKey = "6c90cc84ec280d7a0c59e5da846ada94";

            $datetime = date('Y-m-d H:i:s');

            $sha256Hash = hash('sha256', $merchantCode . $subtotal . $datetime . $apiKey);

            $md5hash = "ff6b3fbcd8376656873ee63d374aa81f";

            // Data untuk permintaan ke API Duitku
            $data = array(
                'merchantcode' => $merchantCode,
                'amount' => $subtotal, // Sesuaikan dengan jumlah yang benar
                'datetime' => $datetime,
                'signature' => $sha256Hash
            );

            $selectedPaymentMethod = '';

            // Konversi data menjadi format JSON
            $data_json = json_encode($data);

            $duitku_url1 = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

            // Inisialisasi curl untuk URL pertama
            $ch1 = curl_init();

            // Set konfigurasi curl untuk URL pertama
            curl_setopt($ch1, CURLOPT_URL, $duitku_url1);
            curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_json);
            curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_json),
                'Authorization: ' . $apiKey, // Tambahkan header otorisasi untuk URL pertama
            ));
            curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);

            // Eksekusi permintaan ke URL pertama
            $response = curl_exec($ch1);

            // Tutup curl untuk URL pertama
            curl_close($ch1);

            // Proses respons dari API Duitku
            if ($response) {
                // Decode respons JSON dari API Duitku
                $result = json_decode($response, true);

                // Periksa apakah permintaan berhasil
                if (isset($result['paymentFee']) && is_array($result['paymentFee'])) {
                    // Metode pembayaran berhasil diambil, tampilkan daftar metode pembayaran
                    $availablePaymentMethods = $result['paymentFee'];

                    echo "<h3>Metode Pembayaran yang Tersedia:</h3>";

                    if (!empty($availablePaymentMethods)) {
                        echo "<div class='payment-method-container'>";
                        foreach ($availablePaymentMethods as $paymentMethod) {
                            $paymentName = $paymentMethod['paymentName'];
                            $paymentImage = $paymentMethod['paymentImage'];
                            $paymentMethod = $paymentMethod['paymentMethod'];
                            echo "<div class='payment-method-card'>";
                            echo "<div class='card-content'>";
                            echo "<a href='payment.php?paymentMethod=$paymentMethod&paymentName=$paymentName&id_transaksi=$id_transaksi&subtotal=$subtotal'>";
                            echo "<img src='" . $paymentImage . "' alt='" . $paymentName . "'>";
                            echo "<p>" . $paymentName . "</p>";
                            echo "</div>";
                            echo "</div>";
                        }
                        echo "</div>";
                    } else {
                        echo "Tidak ada metode pembayaran yang tersedia.";
                    }
                } else {
                    // Menampilkan pesan kesalahan jika permintaan gagal
                    echo "Gagal mengambil daftar metode pembayaran dari API Duitku.";
                }
            } else {
                echo "Gagal melakukan permintaan ke API Duitku.";
            }
        }
    }
} else {
    echo "Tidak ada data transaksi.";
}
?>

<head>
    <link rel="stylesheet" href="./assets/css/invoice.css">
</head>