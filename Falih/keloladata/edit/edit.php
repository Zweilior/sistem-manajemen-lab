<?php
include __DIR__ . '/../koneksi.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_item = (int)$_GET['id'];
} else {
    die("Error: ID Item tidak valid.");
}

$query_data = mysqli_query($koneksi, "SELECT iab.id_item, iab.nama_item, iab.satuan, iab.id_kategori, s.jumlah 
                                      FROM item_alat_bahan iab 
                                      LEFT JOIN stok_lab s ON iab.id_item = s.id_item
                                      WHERE iab.id_item = $id_item");

if (mysqli_num_rows($query_data) > 0) {
    $data = mysqli_fetch_assoc($query_data);
} else {
    die("Error: Data item tidak ditemukan.");
}

$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori_item ORDER BY nama_kategori ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Data Barang</title>
    <link rel="stylesheet" href="edit.css"> 
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-title">
                <img src="../img/ikon.png" alt="Ikon Lab" class="header-icon">
                <h1>KELOLA DATA</h1>
            </div>
            <nav class="breadcrumbs">
                Beranda | Update Kelola Data
            </nav>
        </header>

        <div class="card">
            <form action="../index.php" method="POST" class="form-container">
                
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_item" value="<?php echo $data['id_item']; ?>">

                <div class="form-group">
                    <label for="nama_barang">Nama Barang:</label>
                    <input type="text" id="nama_barang" name="nama_item" placeholder="Masukkan nama barang..." 
                           value="<?php echo htmlspecialchars($data['nama_item']); ?>">
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori:</label>
                    <select id="kategori" name="id_kategori">
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        while ($kategori = mysqli_fetch_assoc($query_kategori)) {
                            $selected = ($kategori['id_kategori'] == $data['id_kategori']) ? 'selected' : '';
                            
                            echo '<option value="' . $kategori['id_kategori'] . '" ' . $selected . '>' 
                                 . htmlspecialchars($kategori['nama_kategori']) 
                                 . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="satuan">Satuan:</label>
                    <input type="text" id="satuan" name="satuan" placeholder="Masukkan Satuan Barang"
                           value="<?php echo htmlspecialchars($data['satuan']); ?>">
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah:</label>
                    <input type="text" id="jumlah" name="jumlah" placeholder="Masukkan dalam bentuk angka"
                           value="<?php echo htmlspecialchars($data['jumlah']); ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">Update Data</button>
                    <a href="../index.php" class="btn">Lihat Data</a> 
                    <a href="../index.php" class="btn">batal</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>