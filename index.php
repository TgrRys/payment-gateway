<?php
include("koneksi.php");

// Fungsi untuk mendapatkan daftar metode pembayaran dari API Duitku
function getPaymentMethods()
{
    // Set kode merchant Anda
    $merchantCode = "DS16784";
    // Set merchant key Anda
    $apiKey = "6c90cc84ec280d7a0c59e5da846ada94";

    // Data untuk permintaan ke API Duitku
    $data = array(
        'merchantcode' => $merchantCode,
        'amount' => 0, // Jumlah akan diisi setelah mendapatkan daftar metode pembayaran
        'datetime' => date('Y-m-d H:i:s'),
    );

    // Konversi data menjadi format JSON
    $data_json = json_encode($data);

    // URL API Duitku untuk mendapatkan daftar metode pembayaran
    $duitku_url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

    // Inisialisasi curl
    $ch = curl_init();

    // Set konfigurasi curl
    curl_setopt($ch, CURLOPT_URL, $duitku_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_json),
        'Authorization: ' . $apiKey, // Tambahkan header otorisasi
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Eksekusi permintaan ke API Duitku
    $response = curl_exec($ch);

    // Tutup curl
    curl_close($ch);

    // Proses respons dari API Duitku
    if ($response) {
        // Decode respons JSON dari API Duitku
        $result = json_decode($response, true);

        // Periksa apakah permintaan berhasil
        if (isset($result['statusCode']) && $result['statusCode'] == 00) {
            // Metode pembayaran berhasil diambil, kembalikan daftar metode pembayaran
            return $result['data'];
        }
    }

    return array(); // Kembalikan array kosong jika gagal
}

// Fungsi untuk menampilkan daftar metode pembayaran
function displayPaymentMethods($paymentMethods)
{
    if (!empty($paymentMethods)) {
        echo "<h2 style='text-align: center;'>Pilih Metode Pembayaran</h2>";
        echo "<form action=\"konfirmasi_pembayaran.php\" method=\"GET\">";
        echo "<label for=\"metode_pembayaran\" style='text-align: center;'>Pilih Metode Pembayaran:</label>";
        echo "<select name=\"metode_pembayaran\" style='display: block; margin: 0 auto;'>";

        foreach ($paymentMethods as $paymentMethod) {
            $paymentCode = $paymentMethod['paymentCode'];
            $paymentName = $paymentMethod['paymentName'];
            echo "<option value=\"$paymentCode\">$paymentName</option>";
        }

        echo "</select><br>";
        echo "<button type=\"submit\" class=\"btn btn-warning\" style='display: block; margin: 0 auto;'>Pilih Metode Pembayaran</button>";
        echo "</form>";
    } else {
        echo "<p style='text-align: center;'>Gagal mengambil daftar metode pembayaran dari API Duitku.</p>";
    }
}

$sql = "SELECT transaksi.id_transaksi, barang.nama_barang, users.nama_user, transaksi.jumlah, barang.harga, transaksi.created_at
        FROM transaksi
        JOIN barang ON transaksi.id_barang = barang.id_barang
        JOIN users ON transaksi.id_user = users.id";

$result = mysqli_query($conn, $sql);

// Inisialisasi total awal
$total = 0;

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="./assets/css/index.css">
</head>
<body>
    <p style='text-align: left;'> <button class="add-button" onclick="location.href='add_transaksi_form.php'">Tambah Transaksi</button> </p>

    <?php
    if (mysqli_num_rows($result) > 0) {
        echo "<table style='text-align: center; margin: 0 auto;'>
        <thead>
            <tr>
                <th>ID Transaksi</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>";

        while ($row = mysqli_fetch_assoc($result)) {
            $jumlah = $row["jumlah"];
            $harga = $row["harga"];
            $subtotal = $jumlah * $harga;

            // Akumulasikan subtotal ke total
            $total += $subtotal;

            echo "<tr>
                <td>" . $row["id_transaksi"] . "</td>
                <td>" . $row["nama_barang"] . "</td>
                <td>" . $row["jumlah"] . "</td>
                <td>Rp " . number_format($row["harga"], 0, ",", ".") . "</td>
                <td>Rp " . number_format($subtotal, 0, ",", ".") . "</td>
                <td><button class=\"pay-button\" onclick=\"location.href='invoice_payment.php?id_transaksi=" . $row["id_transaksi"] . "&metode_pembayaran=duitku'\" style='background-color: #ffc107; font-family: Arial, sans-serif;'>Bayar</button></td>
            </tr>";
        }

        // Tampilkan total di bawah kolom subtotal
        echo "<tr>
            <td colspan='4'>Total</td>
            <td><strong>Rp " . number_format($total, 0, ",", ".") . "</strong></td>
        </tr>";

        echo "</tbody></table>";

        // Mendapatkan daftar metode pembayaran
        $paymentMethods = getPaymentMethods();

        if (count($paymentMethods) > 0) {
            // Menampilkan daftar metode pembayaran
            displayPaymentMethods($paymentMethods);
        }
    } else {
        echo "<p style='text-align: center;'>Tidak ada transaksi.</p>";
    }

    mysqli_close($conn);
    ?>
</body>
</html>
