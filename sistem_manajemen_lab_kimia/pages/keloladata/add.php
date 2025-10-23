<?php
// 1. Hubungkan ke database (PDO)
include __DIR__ . '/../../config/koneksi.php';

// 2. Query untuk mengambil kategori menggunakan PDO
$stmt_kategori = $pdo->query("SELECT * FROM kategori_item ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Barang</title>
    
    <link rel="stylesheet" href="../../assets/css/tambah_keloladata.css"> 
</head>
<body>

    <div class="container">
        <header class="header">
            <div class="header-title">
                <img src="../../assets/img/ikon.png" alt="Ikon Lab" class="header-icon"> 
                <h1>TAMBAH DATA BARANG</h1>
            </div>
        <nav>
            <a href="../keloladata/index.php">Kembali</a> |
            <a href="#" class="active">Tambah Data</a>
        </nav>
        </header>

        <div class="card form-card">
            <form action="index.php" method="POST" class="form-container">
                
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label for="nama_barang">Nama Item:</label>
                    <input type="text" id="nama_barang" name="nama_item" placeholder="Masukkan nama barang..." required>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori:</label>
                    <select id="kategori" name="id_kategori" required>
                        <option value="" disabled selected>-- Pilih Kategori --</option>
                        <?php
                        // 4. Loop kategori menggunakan PDO
                        while ($kategori = $stmt_kategori->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . $kategori['id_kategori'] . '">' 
                                 . htmlspecialchars($kategori['nama_kategori']) 
                                 . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="satuan">Satuan:</label>
                    <input type="text" id="satuan" name="satuan" placeholder="Masukkan Satuan Barang" required>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah:</label>
                    <input type="number" id="jumlah" name="jumlah" placeholder="Masukkan dalam bentuk angka" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">+ Tambah Barang</button>
                    <a href="index.php" class="btn">Lihat Data</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>