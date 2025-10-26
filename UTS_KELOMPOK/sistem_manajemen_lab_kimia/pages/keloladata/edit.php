<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../../index.php'); 
    exit;
}
require '../../config/koneksi.php'; 

$item = null;
$error = '';
$id_item_to_edit = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") { 

    if(isset($_POST['update_data'])) {

        $id_item = isset($_POST['id_item']) ? (int)$_POST['id_item'] : 0; 
        $nama_item = isset($_POST['nama_item']) ? trim($_POST['nama_item']) : '';
        $id_kategori = isset($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : 0;
        $satuan = isset($_POST['satuan']) ? trim($_POST['satuan']) : '';
        $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;

        if ($id_item <= 0 || empty($nama_item) || $id_kategori <= 0 || empty($satuan) || $jumlah < 0) {
            $error = "Semua field harus diisi dengan benar.";
            $id_item_to_edit = $id_item;
        } else {
            $pdo->beginTransaction();
            try {
                $sql1 = "UPDATE item_alat_bahan
                         SET nama_item = ?, id_kategori = ?, satuan = ?
                         WHERE id_item = ?";
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->execute([$nama_item, $id_kategori, $satuan, $id_item]);

                $sql2 = "UPDATE stok_lab SET jumlah = ?, tanggal_update = NOW() WHERE id_item = ?";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([$jumlah, $id_item]);

                $pdo->commit();

                header("Location: ../keloladata/index.php?status=update_sukses"); 
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Gagal mengupdate data: " . $e->getMessage();
                $id_item_to_edit = $id_item;
            }
        }
    } else {
    }
}

if ($id_item_to_edit === null && isset($_GET['id'])) { 
     $id_item_to_edit = (int)$_GET['id'];
}

if ($id_item_to_edit !== null && $id_item_to_edit > 0) {
    $sql_get = "SELECT i.id_item, i.nama_item, i.id_kategori, i.satuan, s.jumlah
                FROM item_alat_bahan i
                LEFT JOIN stok_lab s ON i.id_item = s.id_item
                WHERE i.id_item = ?";

    $stmt_get = $pdo->prepare($sql_get);
    $stmt_get->execute([$id_item_to_edit]);
    $item = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$item && empty($error)) { 
        $error = "Data item tidak ditemukan.";
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($error) && $id_item_to_edit > 0) {
         $item['nama_item'] = $_POST['nama_item'] ?? $item['nama_item']; 
         $item['id_kategori'] = $_POST['id_kategori'] ?? $item['id_kategori'];
         $item['satuan'] = $_POST['satuan'] ?? $item['satuan'];
         $item['jumlah'] = $_POST['jumlah'] ?? $item['jumlah'];
    }


} elseif (empty($error) && $_SERVER["REQUEST_METHOD"] != "POST") { 
     $error = "ID Item tidak valid atau tidak disediakan.";
}


try {
    $sql_kategori = "SELECT id_kategori, nama_kategori FROM kategori_item ORDER BY nama_kategori ASC";
    $stmt_kategori = $pdo->query($sql_kategori);
    $semua_kategori = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $semua_kategori = []; 
    $error .= " Gagal mengambil daftar kategori."; 
}


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
        <nav class="breadcrumbs">
            <a href="../keloladata/index.php">Kembali</a> |
            <a href="#" class="active">Update Kelola Data</a>
        </nav>
    </header>

    <main>
        <section class="card form-card"> <?php if (!empty($error)): ?>
            <div class="alert-error" style="color: red; text-align: center; margin-bottom: 15px; background-color: #ffebee; border: 1px solid #ef9a9a; padding: 10px; border-radius: 8px;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($item): ?>

                <form class="form-container" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_item_to_edit; ?>" method="POST">

                    <input type="hidden" name="id_item" value="<?= $item['id_item'] ?>">

                    <div class="form-group">
                        <label for="nama-item">Nama Barang:</label>
                        <input type="text" id="nama-item" name="nama_item"
                               value="<?= htmlspecialchars($item['nama_item'] ?? '') ?>"
                               placeholder="Masukkan nama barang..." required>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori:</label>
                        <select id="kategori" name="id_kategori" required>
                            <option value="" disabled>-- Pilih Kategori --</option> <?php foreach ($semua_kategori as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>"
                                    <?= (isset($item['id_kategori']) && $kat['id_kategori'] == $item['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="satuan">Satuan:</label>
                        <input type="text" id="satuan" name="satuan"
                               value="<?= htmlspecialchars($item['satuan'] ?? '') ?>"
                               placeholder="Masukkan Satuan Barang" required>
                    </div>

                    <div class="form-group">
                        <label for="jumlah">Jumlah:</label>
                        <input type="number" id="jumlah" name="jumlah"
                               value="<?= htmlspecialchars($item['jumlah'] ?? '0') ?>"
                               placeholder="Masukkan dalam bentuk angka" required min="0">
                    </div>

                    <div class="button-group">
                        <button type="submit" name="update_data" class="btn btn-primary">
                            Update Data
                        </button>
                        <a href="../keloladata/index.php" class="btn btn-secondary">
                            Lihat Data
                        </a>
                        <a href="../keloladata/index.php" class="btn btn-secondary">
                            Batal
                        </a>
                    </div>
                </form>

            <?php elseif(empty($error)): ?>
                 <p style="text-align: center;">Tidak dapat memuat data item.</p>
                 <div class="button-group">
                     <a href="../keloladata/index.php" class="btn">Kembali</a>
                 </div>
            <?php endif; ?>

        </section>
    </main>

</div>
</body>
</html>