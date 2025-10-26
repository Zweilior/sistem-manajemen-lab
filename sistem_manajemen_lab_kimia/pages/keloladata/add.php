<?php
include __DIR__ . '/../../config/koneksi.php';                                                  // Sertakan file koneksi PDO ($pdo)                         

try {                                                                                           // Mencoba mengambil semua kategori dari tabel kategori_item
    $stmt_kategori = $pdo->query("SELECT * FROM kategori_item ORDER BY nama_kategori ASC");
    $semua_kategori = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC); 
} catch (PDOException $e) {                                                                     // Menangkap error jika query gagal
    error_log("Gagal mengambil kategori: " . $e->getMessage());
    $semua_kategori = []; 
}

?>
<!DOCTYPE html>
<html lang="id">
<head>                                                                                          <!-- Header HTML umum -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Barang</title>

    <link rel="stylesheet" href="../../assets/css/tambah_keloladata.css">                     <!-- Link file CSS khusus halaman tambah kelola data -->          
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container">                                                                     <!-- Kontainer utama halaman -->                    
        <header class="header">
            <div class="header-title">
                <img src="../../assets/img/ikon.png" alt="Ikon Lab" class="header-icon">
                <h1>TAMBAH DATA BARANG</h1>
            </div>
            <nav class="breadcrumbs">                                                           <!-- Navigasi breadcrumb -->
                <a href="../dashboard.php">Beranda</a> |
                <a href="#" class="active">Tambah Data Barang</a>
            </nav>
        </header>

        <div class="card form-card">                                                            <!-- Kartu/form untuk tambah data barang -->
            <form action="index.php" method="POST" class="form-container">

                <input type="hidden" name="action" value="create">

                <div class="form-group">                                                        <!-- Grup input untuk nama item -->
                    <label for="nama_barang">Nama Item:</label>
                    <input type="text" id="nama_barang" name="nama_item" placeholder="Masukkan nama barang..." required>
                </div>

                <div class="form-group">                                                        <!-- Grup input untuk kategori -->           
                    <label for="kategori">Kategori:</label>
                    <select id="kategori" name="id_kategori" required>
                        <option value="" disabled selected>-- Pilih Kategori --</option>
                        <?php
                        foreach ($semua_kategori as $kategori) {                                // Loop melalui semua kategori untuk opsi select
                            echo '<option value="' . htmlspecialchars($kategori['id_kategori']) . '">'
                                 . htmlspecialchars($kategori['nama_kategori'])
                                 . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">                                                        <!-- Grup input untuk satuan -->                
                    <label for="satuan">Satuan:</label>
                    <input type="text" id="satuan" name="satuan" placeholder="Masukkan Satuan Barang" required>
                </div>

                <div class="form-group">                                                        <!-- Grup input untuk jumlah -->                  
                    <label for="jumlah">Jumlah:</label>
                    <input type="text" id="jumlah" name="jumlah" placeholder="Masukkan dalam bentuk angka" required
                           pattern="[0-9]+" title="Hanya masukkan angka positif"> </div>

                <div class="button-group">                                                     <!-- Grup tombol aksi -->            
                    <button type="submit" class="btn btn-primary">+ Tambah Barang</button>
                    <a href="index.php" class="btn btn-secondary">Lihat Data</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>