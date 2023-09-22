<!DOCTYPE html>
<html>
<head>
    <title>Tambah Transaksi</title>
    <link rel="stylesheet" href="./assets/css/form.css">
</head>
<body>
    <div class="card">
        <h2>Tambah Transaksi</h2>
        <form action="add_transaksi.php" method="POST">
            <label for="id_barang">Pilih Barang:</label>
            <select name="id_barang">
                <?php
                include("koneksi.php");

                $sql_barang = "SELECT id_barang, nama_barang FROM barang";
                $result_barang = mysqli_query($conn, $sql_barang);

                if (mysqli_num_rows($result_barang) > 0) {
                    while ($row_barang = mysqli_fetch_assoc($result_barang)) {
                        echo "<option value='" . $row_barang["id_barang"] . "'>" . $row_barang["nama_barang"] . "</option>";
                    }
                }

                mysqli_close($conn);
                ?>
            </select>

            <label for="id_user">Pembeli:</label>
            <select name="id_user">
                <?php
                include("koneksi.php");

                $sql_users = "SELECT id, nama_user FROM users";
                $result_users = mysqli_query($conn, $sql_users);

                if (mysqli_num_rows($result_users) > 0) {
                    while ($row_users = mysqli_fetch_assoc($result_users)) {
                        echo "<option value='" . $row_users["id"] . "'>" . $row_users["nama_user"] . "</option>";
                    }
                }

                mysqli_close($conn);
                ?>
            </select>

            <label for="jumlah">Jumlah:</label>
            <input type="number" id="jumlah" name="jumlah" required>

            <input type="submit" value="Simpan">
        </form>
    </div>
</body>
</html>
