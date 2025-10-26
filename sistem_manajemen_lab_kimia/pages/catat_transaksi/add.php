<?php
// --- 1. MEMULAI SESSION DAN CEK LOGIN ADMIN ---
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../../index.php');
    exit;
}

// --- 2. MENYAMBUNG KE DATABASE ---
require_once __DIR__ . '/../../config/koneksi.php'; 
// Pastikan path ini benar
$error_message = ''; 

// --- 3. PROSES FORM SAAT DIKIRIM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang   = isset($_POST['nama_barang']) ? trim($_POST['nama_barang']) : '';
    $jumlah_trans  = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0; 
    $jenis_raw     = isset($_POST['keterangan']) ? $_POST['keterangan'] : ''; 
    $jenis         = ($jenis_raw === 'masuk') ? 'Masuk' : (($jenis_raw === 'keluar') ? 'Keluar' : '');
    $tanggal_input = isset($_POST['tanggal']) ? $_POST['tanggal'] : ''; 
    $tanggal_dt    = null; 

    // Validasi input
    if (empty($nama_barang)) { $error_message = "Nama Barang wajib diisi."; }
    elseif ($jumlah_trans <= 0) { $error_message = "Jumlah harus angka positif."; }
    elseif (empty($jenis)) { $error_message = "Keterangan (Pemasukan/Pengeluaran) wajib dipilih."; }
    elseif (empty($tanggal_input)) { $error_message = "Tanggal Transaksi wajib diisi."; }
    else {
        $date_obj = DateTime::createFromFormat('m/d/Y', $tanggal_input);
        if ($date_obj !== false) {
            $tanggal_dt = $date_obj->format('Y-m-d H:i:s'); 
        } else {
             $error_message = "Format Tanggal Transaksi tidak valid. Gunakan mm/dd/yyyy.";
        }
    }

    // Jika tidak ada error, lanjutkan proses penyimpanan
    if (empty($error_message)) {
        try {
            $pdo->beginTransaction();

            // Cek apakah barang sudah ada di tabel item_alat_bahan
            $stmt_check = $pdo->prepare('SELECT id_item FROM item_alat_bahan WHERE nama_item = :nama_item');
            $stmt_check->execute([':nama_item' => $nama_barang]);
            $existing_item = $stmt_check->fetch(PDO::FETCH_ASSOC);

            // Variabel untuk menyimpan id_item dan id_stok
            $id_item = null;
            $id_stok = null;

            // Jika barang belum ada, tambahkan ke item_alat_bahan dan stok_lab
            if (!$existing_item) {
                $default_kategori = 17; 
                $default_satuan = 'unit'; 
                
                // Tambah ke item_alat_bahan
                $stmt_insert_item = $pdo->prepare('INSERT INTO item_alat_bahan (nama_item, id_kategori, satuan) VALUES (:nama_item, :id_kategori, :satuan)');
                $stmt_insert_item->execute([
                    ':nama_item' => $nama_barang,
                    ':id_kategori' => $default_kategori,
                    ':satuan' => $default_satuan
                ]);
                $id_item = $pdo->lastInsertId();

                // Tambah ke stok_lab dengan jumlah awal sesuai jenis transaksi
                $initial_jumlah = ($jenis === 'Masuk') ? $jumlah_trans : 0;
                if ($jenis === 'Keluar') {
                    throw new Exception("Tidak dapat melakukan pengeluaran untuk barang yang belum pernah ada stoknya.");
                }
                $stmt_insert_stock = $pdo->prepare('INSERT INTO stok_lab (id_item, jumlah, kondisi, tanggal_update) VALUES (:id_item, :jumlah, "Baik", NOW())');
                $stmt_insert_stock->execute([':id_item' => $id_item, ':jumlah' => $initial_jumlah]);
                $id_stok = $pdo->lastInsertId();
                // Jika barang sudah ada, perbarui stok sesuai jenis transaksi
            } else {
                $id_item = $existing_item['id_item'];

                // Ambil data stok saat ini
                $stmt_get_stock = $pdo->prepare('SELECT id_stok, jumlah FROM stok_lab WHERE id_item = :id_item');
                $stmt_get_stock->execute([':id_item' => $id_item]);
                $stok = $stmt_get_stock->fetch(PDO::FETCH_ASSOC);

                // Perbarui stok sesuai jenis transaksi
                if ($stok) {
                    $id_stok = $stok['id_stok'];
                    $stok_sebelum = $stok['jumlah'];
                    if ($jenis === 'Masuk') {
                        $stok_baru = $stok_sebelum + $jumlah_trans;
                    } else { 
                        $stok_baru = $stok_sebelum - $jumlah_trans;
                        if ($stok_baru < 0) {
                            throw new Exception("Stok tidak mencukupi (" . $stok_sebelum . " tersedia) untuk pengeluaran " . $jumlah_trans . ".");
                        }
                    }
                    $stmt_update_stock = $pdo->prepare('UPDATE stok_lab SET jumlah = :jumlah, tanggal_update = NOW() WHERE id_stok = :id_stok');
                    $stmt_update_stock->execute([':jumlah' => $stok_baru, ':id_stok' => $id_stok]);
                    // Jika stok belum ada (seharusnya tidak terjadi karena barang ada), buat entri baru
                } else {
                     $initial_jumlah = ($jenis === 'Masuk') ? $jumlah_trans : 0;
                     if ($jenis === 'Keluar') {
                         throw new Exception("Stok tidak mencukupi (barang belum pernah ada stok).");
                     }
                    $stmt_insert_stock = $pdo->prepare('INSERT INTO stok_lab (id_item, jumlah, kondisi, tanggal_update) VALUES (:id_item, :jumlah, "Baik", NOW())');
                    $stmt_insert_stock->execute([':id_item' => $id_item, ':jumlah' => $initial_jumlah]);
                    $id_stok = $pdo->lastInsertId();
                }
            }

            // Catat transaksi di tabel transaksi_stok
            if ($id_stok !== null) {
                $stmt_insert_trans = $pdo->prepare('INSERT INTO transaksi_stok (id_stok, jenis_transaksi, jumlah, tanggal_transaksi, keterangan) VALUES (:id_stok, :jenis, :jumlah, :tanggal, :keterangan)');
                $stmt_insert_trans->execute([
                    ':id_stok' => $id_stok,
                    ':jenis' => $jenis,
                    ':jumlah' => $jumlah_trans,
                    ':tanggal' => $tanggal_dt,
                    ':keterangan' => $jenis
                ]);
                // Jika id_stok null, lempar error
            } else {
                 throw new Exception("Gagal mendapatkan ID Stok untuk transaksi.");
            }
            
            // Commit transaksi
            $pdo->commit();
            header('Location: index.php?status=success'); 
            exit;
            
            // Tangkap error dan rollback jika ada masalah
        } catch (Exception $e) { 
            $pdo->rollBack();
            $error_message = 'Gagal menyimpan data: ' . $e->getMessage();
            error_log('Error adding transaction: ' . $e->getMessage());
        }
    } 
} 
?>

// --- 4. FORM HTML UNTUK MENAMBAH TRANSAKSI ---
<!DOCTYPE html>
<html lang="id">
<head>                                                                          <!-- Bagian kepala dokumen HTML --->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Transaksi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/style_update_transaksi.css">  <!-- Link file CSS khusus halaman tambah transaksi -->
</head>
<body>
                                                                                <!-- Bagian tubuh dokumen HTML --->
    <header class="main-header">
        <div class="logo">                                                      <!-- Bagian logo dan judul di header --->
            <img src="../../assets/img/ikon.png" alt="Logo" class="logo-img">
            <h1>TAMBAH DATA TRANSAKSI</h1>
        </div>
        <nav class="breadcrumbs"> <a href="../dashboard.php">Beranda</a> |      <!-- Breadcrumbs navigasi --->
            <a href="index.php">Catat Transaksi</a> | <a href="#" class="active">Tambah Data Transaksi</a> </nav>
    </header>

    <main>
        <section class="card form-card">                                        <!-- Kartu/form untuk tambah data transaksi -->

            <?php if (!empty($error_message)): ?>
                <div style="color: red; text-align: center; margin-bottom: 15px; background-color: #ffebee; border: 1px solid #ef9a9a; padding: 10px; border-radius: 8px;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form class="form-container" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">      <!-- Form tambah data transaksi -->

                <div class="form-group">                                        <!-- Grup input untuk nama barang --->
                    <label for="nama_barang">Nama Barang :</label>
                    <input type="text"
                           id="nama_barang"
                           name="nama_barang"
                           required
                           placeholder="Masukkan nama barang..."
                           value="<?php echo isset($_POST['nama_barang']) ? htmlspecialchars($_POST['nama_barang']) : ''; ?>"> </div>

                <div class="form-group">                                    <!-- Grup input untuk tanggal transaksi --->
                    <label for="tanggal">Tanggal Transaksi :</label>
                    <input type="text"
                           id="tanggal"
                           name="tanggal"
                           required
                           placeholder="mm/dd/yyyy"
                           value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : date('m/d/Y'); ?>"> </div>

                <div class="form-group">                                <!-- Grup input untuk jumlah transaksi --->                     
                    <label for="jumlah">Jumlah :</label>
                    <input type="text"
                           id="jumlah"
                           name="jumlah"
                           required
                           pattern="[1-9][0-9]*" title="Masukkan angka positif"
                           placeholder="Masukkan dalam bentuk angka"
                           value="<?php echo isset($_POST['jumlah']) ? htmlspecialchars($_POST['jumlah']) : ''; ?>"> </div>

                <div class="form-group">                            <!-- Grup input untuk keterangan transaksi --->                         
                    <label for="keterangan">Keterangan :</label>
                    <select name="keterangan" id="keterangan" required>
                        <option value="" disabled <?php echo (!isset($_POST['keterangan']) || $_POST['keterangan'] == '') ? 'selected' : ''; ?>>Pilih Jenis Transaksi</option>
                        <option value="masuk" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'masuk') ? 'selected' : ''; ?>>Pemasukan</option>
                        <option value="keluar" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'keluar') ? 'selected' : ''; ?>>Pengeluaran</option>
                    </select>
                </div>

                <div class="form-actions"> <button type="submit" class="btn btn-primary">                <!-- Tombol submit form --->
                        + Tambah Transaksi
                    </button>
                    <a href="index.php" class="btn btn-secondary">                             <!-- Tombol batal kembali ke daftar transaksi --->
                        Lihat Data
                    </a>
                </div>

            </form>
        </section>
    </main>

</body>
</html>