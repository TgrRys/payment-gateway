<?php
include("koneksi.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_barang = $_POST["id_barang"];
    $id_user = $_POST["id_user"];
    $jumlah = $_POST["jumlah"];

    $sql = "INSERT INTO transaksi (id_barang, id_user, jumlah) VALUES ('$id_barang', '$id_user', '$jumlah')";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
