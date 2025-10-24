<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../../index.php');
    exit;
}
// Lokasi file: barang/edit.php
require '../../config/koneksi.php'; // <-- SUDAH BENAR: Menggunakan koneksi PDO

$item = null;
$error = '';
$id_item_to_edit = null;

// --- 1. LOGIKA PROSES UPDATE (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_data'])) {
    
    // Ambil semua data dari form
    $id_item = (int)$_POST['id_item'];
    $nama_item = $_POST['nama_item'];
    $id_kategori = (int)$_POST['id_kategori'];
    $satuan = $_POST['satuan'];
    $jumlah = (int)$_POST['jumlah'];

    // SUDAH BENAR: Menggunakan PDO Transaction
    $pdo->beginTransaction();
    try {
        // STEP 1: Update tabel item_alat_bahan
        // SUDAH BENAR: Query aman dengan placeholder (?)
        $sql1 = "UPDATE item_alat_bahan 
                 SET nama_item = ?, id_kategori = ?, satuan = ? 
                 WHERE id_item = ?";
        $stmt1 = $pdo->prepare($sql1);
        // SUDAH BENAR: Execute dengan parameter terpisah
        $stmt1->execute([$nama_item, $id_kategori, $satuan, $id_item]);

        // STEP 2: Update tabel stok_lab
        $sql2 = "UPDATE stok_lab SET jumlah = ?, tanggal_update = NOW() WHERE id_item = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$jumlah, $id_item]);
        
        // (Opsional) Anda bisa menambah record di transaksi_stok
        // ... (tapi untuk edit, mungkin tidak perlu, kecuali jumlahnya berubah) ...

        // SUDAH BENAR: Commit transaksi
        $pdo->commit();

        // Redirect ke halaman utama
        header("Location: index.php?status=update_sukses");
        exit;

    } catch (PDOException $e) {
        // SUDAH BENAR: Rollback jika gagal
        $pdo->rollBack();
        $error = "Gagal mengupdate data: " . $e->getMessage();
    }
}

// --- 2. LOGIKA AMBIL DATA UNTUK FORM (GET) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id_item_to_edit = (int)$_GET['id'];
    
    // Query untuk mengambil data item + stoknya
    // SUDAH BENAR: Query aman dengan placeholder (?)
    $sql_get = "SELECT i.id_item, i.nama_item, i.id_kategori, i.satuan, s.jumlah 
                FROM item_alat_bahan i
                LEFT JOIN stok_lab s ON i.id_item = s.id_item
                WHERE i.id_item = ?";
    
    // SUDAH BENAR: Prepare dan execute
    $stmt_get = $pdo->prepare($sql_get);
    $stmt_get->execute([$id_item_to_edit]);
    // SUDAH BENAR: Fetch data
    $item = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $error = "Data item tidak ditemukan.";
        $item = null; // Pastikan item null jika tidak ketemu
    }
}

// Ambil semua kategori untuk dropdown
// SUDAH BENAR: Menggunakan PDO query
$sql_kategori = "SELECT id_kategori, nama_kategori FROM kategori_item ORDER BY nama_kategori ASC";
$stmt_kategori = $pdo->query($sql_kategori);
$semua_kategori = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

$pdo = null; // Tutup koneksi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Barang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/css/edit_keloladata.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div class="header-title">
            <img src="../../assets/img/ikon.png" alt="Logo" class="header-icon">
            <h1>KELOLA DATA</h1>
        </div>
        <nav>
            <a href="../keloladata/index.php">Kembali</a> |
            <a href="#" class="active">Update Kelola Data</a>
        </nav>
    </header>

    <main>
        <section class="card">
            
            <?php if ($error): ?>
                <div class="alert-error" style="color: red; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($item): ?>
                
                <form class="form-container" action="edit.php" method="POST">
                    
                    <input type="hidden" name="id_item" value="<?= $item['id_item'] ?>">
                    
                    <div class="form-group">
                        <label for="nama-item">Nama Barang:</label>
                        <input type="text" id="nama-item" name="nama_item" 
                               value="<?= htmlspecialchars($item['nama_item']) ?>" 
                               placeholder="Masukkan nama barang..." required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori:</label>
                        <select id="kategori" name="id_kategori" required>
                            <?php foreach ($semua_kategori as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>" 
                                    <?= ($kat['id_kategori'] == $item['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="satuan">Satuan:</label>
                        <input type="text" id="satuan" name="satuan" 
                               value="<?= htmlspecialchars($item['satuan']) ?>" 
                               placeholder="Masukkan Satuan Barang" required>
                    </div>

                    <div class="form-group">
                        <label for="jumlah">Jumlah:</label>
                        <input type="number" id="jumlah" name="jumlah" 
                               value="<?= htmlspecialchars($item['jumlah']) ?>" 
                               placeholder="Masukkan dalam bentuk angka" required min="0">
                    </div>

                    <div class="button-group">
                        <button type="submit" name="update_data" class="btn">
                            Update Data
                        </button>
                        <a href="index.php" class="btn">
                            Lihat Data
                        </a>
                        <a href="index.php" class="btn">
                            batal
                        </a>
                    </div>
                </form>

            <?php else: ?>
                <p style="text-align: center;">Data item tidak ditemukan atau ID tidak valid.</p>
                <div class="button-group">
                    <a href="../index.php" class="btn">Kembali ke Beranda</a>
                </div>
            <?php endif; ?>

        </section>
    </main>

</div> </body>
</html>