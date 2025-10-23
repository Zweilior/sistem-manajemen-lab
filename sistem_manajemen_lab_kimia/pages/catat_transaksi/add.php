<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php'; // file yang Anda buat -> menyediakan $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = isset($_POST['nama_barang']) ? trim($_POST['nama_barang']) : '';
    $jumlah     = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
    $jenis_raw  = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';
    $jenis      = ($jenis_raw === 'masuk') ? 'Masuk' : (($jenis_raw === 'keluar') ? 'Keluar' : '');
    $tanggal    = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
    $tanggal_dt = $tanggal . ' ' . date('H:i:s');

    if (!empty($nama_barang) && $jumlah > 0 && $jenis !== '') {
        try {
            $pdo->beginTransaction();

            // Cek apakah barang sudah ada
            $stmt = $pdo->prepare('SELECT id_item FROM item_alat_bahan WHERE nama_item = :nama_item');
            $stmt->execute([':nama_item' => $nama_barang]);
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing_item) {
                // Insert barang baru ke item_alat_bahan
                $stmt = $pdo->prepare('INSERT INTO item_alat_bahan (nama_item, id_kategori) VALUES (:nama_item, :id_kategori)');
                $stmt->execute([
                    ':nama_item' => $nama_barang,
                    ':id_kategori' => 16 // kategori default, sesuaikan dengan kebutuhan
                ]);
                $id_item = $pdo->lastInsertId();

                // Insert ke stok_lab
                $stmt = $pdo->prepare('INSERT INTO stok_lab (id_item, jumlah) VALUES (:id_item, :jumlah)');
                $stmt->execute([
                    ':id_item' => $id_item,
                    ':jumlah' => $jumlah
                ]);
                $id_stok = $pdo->lastInsertId();
            } else {
                $id_item = $existing_item['id_item'];
                
                // Cek/buat stok_lab entry
                $stmt = $pdo->prepare('SELECT id_stok, jumlah FROM stok_lab WHERE id_item = :id_item');
                $stmt->execute([':id_item' => $id_item]);
                $stok = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($stok) {
                    $id_stok = $stok['id_stok'];
                    $stok_baru = ($jenis === 'Masuk') ? $stok['jumlah'] + $jumlah : $stok['jumlah'] - $jumlah;
                    if ($stok_baru < 0) $stok_baru = 0;

                    // Update stok yang ada
                    $stmt = $pdo->prepare('UPDATE stok_lab SET jumlah = :jumlah WHERE id_stok = :id_stok');
                    $stmt->execute([':jumlah' => $stok_baru, ':id_stok' => $id_stok]);
                } else {
                    // Buat stok baru
                    $stmt = $pdo->prepare('INSERT INTO stok_lab (id_item, jumlah) VALUES (:id_item, :jumlah)');
                    $stmt->execute([':id_item' => $id_item, ':jumlah' => $jumlah]);
                    $id_stok = $pdo->lastInsertId();
                }
            }

            // Insert transaksi
            $stmt = $pdo->prepare('INSERT INTO transaksi_stok (id_stok, jenis_transaksi, jumlah, tanggal_transaksi, keterangan) VALUES (:id_stok, :jenis, :jumlah, :tanggal, :keterangan)');
            $stmt->execute([
                ':id_stok' => $id_stok,
                ':jenis' => $jenis,
                ':jumlah' => $jumlah,
                ':tanggal' => $tanggal_dt,
                ':keterangan' => $_POST['keterangan'] ?? ''
            ]);

            $pdo->commit();
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Error adding transaction: ' . $e->getMessage());
            die('Terjadi kesalahan saat menyimpan data.');
        }
    }
}

// ambil daftar barang untuk select (gunakan PDO)
$stmt = $pdo->query("SELECT s.id_stok, COALESCE(i.nama_item, '-') AS nama_item, s.jumlah FROM stok_lab s LEFT JOIN item_alat_bahan i ON s.id_item = i.id_item ORDER BY i.nama_item ASC");
$stok_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Transaksi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/css/style_update_transaksi.css">
</head>
<body>

    <header class="main-header">
        <div class="logo">
            <img src="../../assets/img/ikon.png" alt="Logo" class="logo-img">
            <h1>TAMBAH DATA TRANSAKSI</h1>
        </div>
        <nav>
            <a href="../catat_transaksi/index.php">Kembali</a> |
            <a href="#">Tambah Data Transaksi</a>
        </nav>
    </header>

    <main>
        <section class="card form-card">
            
            <form class="form-container" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                
                <div class="form-group">
                    <label for="nama_barang">Nama Barang :</label>
                    <input type="text" 
                        id="nama_barang" 
                        name="nama_barang" 
                        required 
                        placeholder="Masukkan nama barang...">
                </div>
                
                <div class="form-group">
                    <label for="tanggal">Tanggal Transaksi :</label>
                    <input type="date" 
                       id="tanggal" 
                       name="tanggal" 
                       required 
                       value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah :</label>
                    <input type="number" 
                       id="jumlah" 
                       name="jumlah" 
                       required 
                       min="1" 
                       placeholder="Masukkan dalam bentuk angka">
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan :</label>
                    <select name="keterangan" id="keterangan" required>
                        <option value="">Pilih Jenis Transaksi</option>
                        <option value="masuk">Pemasukan</option>
                        <option value="keluar">Pengeluaran</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                       + Tambah Transaksi
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        Lihat Data
                    </a>
                </div>

            </form>
        </section>
    </main>

</body>
</html>