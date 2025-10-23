<?php
// --- AWAL BLOK LOGIKA (SEMUA SUDAH PDO) ---

// 1. Sertakan koneksi PDO
include __DIR__ . '/koneksi.php'; // $pdo akan dibuat di sini

try {
    // 2. LOGIKA CREATE (TAMBAH DATA)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
        
        $pdo->beginTransaction(); 

        $query1 = "INSERT INTO item_alat_bahan (nama_item, id_kategori, satuan) VALUES (?, ?, ?)";
        $stmt1 = $pdo->prepare($query1);
        $stmt1->execute([
            $_POST['nama_item'], 
            $_POST['id_kategori'], 
            $_POST['satuan']
        ]);
        
        $new_id_item = $pdo->lastInsertId(); 

        $query2 = "INSERT INTO stok_lab (id_item, jumlah, kondisi, tanggal_update) VALUES (?, ?, 'Baik', NOW())";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->execute([
            $new_id_item, 
            $_POST['jumlah']
        ]);

        $new_id_stok = $pdo->lastInsertId(); 

        $query3 = "INSERT INTO transaksi_stok (id_stok, jenis_transaksi, jumlah, tanggal_transaksi, keterangan)
                   VALUES (?, 'Masuk', ?, NOW(), 'Pengadaan awal')";
        $stmt3 = $pdo->prepare($query3);
        $stmt3->execute([
            $new_id_stok, 
            $_POST['jumlah']
        ]);

        $pdo->commit(); 
        header('Location: index.php'); 
        exit;
    }

    // 3. LOGIKA UPDATE (EDIT DATA)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
        
        $pdo->beginTransaction();

        $query1 = "UPDATE item_alat_bahan SET nama_item = ?, id_kategori = ?, satuan = ? WHERE id_item = ?";
        $stmt1 = $pdo->prepare($query1);
        $stmt1->execute([
            $_POST['nama_item'], 
            $_POST['id_kategori'], 
            $_POST['satuan'], 
            $_POST['id_item']
        ]);
        
        $query2 = "UPDATE stok_lab SET jumlah = ?, tanggal_update = NOW() WHERE id_item = ?";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->execute([
            $_POST['jumlah'], 
            $_POST['id_item']
        ]);

        $pdo->commit();
        header('Location: index.php');
        exit;
    }

    // 4. LOGIKA DELETE (HAPUS DATA)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id_item = (int)$_GET['id'];
        
        $pdo->beginTransaction();

        $stmtStok = $pdo->prepare("SELECT id_stok FROM stok_lab WHERE id_item = ?");
        $stmtStok->execute([$id_item]);
        $stok_data = $stmtStok->fetch();

        if ($stok_data) {
            $id_stok = $stok_data['id_stok'];
            $pdo->prepare("DELETE FROM transaksi_stok WHERE id_stok = ?")->execute([$id_stok]);
        }
        
        $pdo->prepare("DELETE FROM stok_lab WHERE id_item = ?")->execute([$id_item]);
        $pdo->prepare("DELETE FROM item_alat_bahan WHERE id_item = ?")->execute([$id_item]);

        $pdo->commit();
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); 
    }
    die("Database error: " . $e->getMessage());
}


// --- AWAL LOGIKA PAGINATION (PDO) ---

$jumlah_data_per_halaman = 3; 

$sql_where = "";
$join_clause = " LEFT JOIN kategori_item k ON iab.id_kategori = k.id_kategori
                 LEFT JOIN stok_lab s ON iab.id_item = s.id_item "; 
$params_url = []; 
$params_sql = []; 

if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $sql_where .= " WHERE (iab.id_item LIKE ? OR iab.nama_item LIKE ?) ";
    $params_sql[] = "%$search%";
    $params_sql[] = "%$search%";
    $params_url[] = "search=" . urlencode($search);
}

if (!empty($_GET['kategori']) && $_GET['kategori'] != 'semua') { 
    $kategori_id = (int)$_GET['kategori'];
    $sql_where .= ($sql_where == "") ? " WHERE " : " AND ";
    $sql_where .= " iab.id_kategori = ? "; 
    $params_sql[] = $kategori_id;
    $params_url[] = "kategori=$kategori_id";
}

$query_string = implode("&", $params_url);
$link_prefix = $query_string ? "?$query_string&" : "?";

$stmt_total = $pdo->prepare("SELECT COUNT(*) as total FROM item_alat_bahan iab $join_clause $sql_where");
$stmt_total->execute($params_sql);
$total_data = $stmt_total->fetchColumn();

$total_halaman = ceil($total_data / $jumlah_data_per_halaman);
if ($total_halaman < 1) $total_halaman = 1; 

$halaman_aktif = (isset($_GET['halaman'])) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;
if ($halaman_aktif > $total_halaman) $halaman_aktif = $total_halaman;

$data_awal = ($halaman_aktif - 1) * $jumlah_data_per_halaman;

$sql_data = "SELECT iab.id_item, iab.nama_item, iab.satuan, k.nama_kategori, s.jumlah 
             FROM item_alat_bahan iab 
             $join_clause 
             $sql_where 
             ORDER BY iab.id_item ASC 
             LIMIT $data_awal, $jumlah_data_per_halaman";
             
$stmt_data = $pdo->prepare($sql_data);
$stmt_data->execute($params_sql);

$data_di_halaman_ini = $stmt_data->rowCount();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Laboratorium</title>
    <link rel="stylesheet" href="keloladata.css">
</head>
<body>

    <div class="container">
        
        <header class="header">
            <div class="header-title">
                <img src="./img/ikon.png" alt="Ikon Lab" class="header-icon">
                <h1>KELOLA DATA</h1>
            </div>
            
            <nav class="breadcrumbs">
                <a href="../index.php" style="color: inherit; text-decoration: none;">Beranda</a> | Kelola Data
            </nav>
            
        </header>

        <form class="card filter-card" method="GET" action="index.php" id="filterForm">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Kode atau nama barang...."
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="semua" <?php echo (!isset($_GET['kategori']) || $_GET['kategori'] == 'semua') ? 'selected' : ''; ?>>Semua Kategori</option>
                    <option value="11" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '11') ? 'selected' : ''; ?>>Bahan - Asam</option>
                    <option value="12" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '12') ? 'selected' : ''; ?>>Bahan - Basa</option>
                    <option value="13" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '13') ? 'selected' : ''; ?>>Bahan - Netral & Garam</option>
                    <option value="14" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '14') ? 'selected' : ''; ?>>Bahan - Pelarut</option>
                    <option value="15" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '15') ? 'selected' : ''; ?>>Bahan - Indikator</option>
                    <option value="16" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '16') ? 'selected' : ''; ?>>Peralatan Gelas</option>
                    <option value="17" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '17') ? 'selected' : ''; ?>>Peralatan Umum</option>
                    <option value="18" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == '18') ? 'selected' : ''; ?>>Instrumen Lab</option>
                </select>
            </div>
            
            <a href="tambah/tambah.php" class="btn btn-primary">+ Tambah Barang</a>
        </form>

        <div class="card data-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php
                    if ($data_di_halaman_ini > 0):
                        $nomor = $data_awal + 1;
                        
                        while ($data = $stmt_data->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?php echo $nomor++; ?></td>
                        <td><?php echo htmlspecialchars($data['id_item']); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_item']); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_kategori']); ?></td> 
                        <td><?php echo htmlspecialchars($data['satuan']); ?></td>
                        <td><?php echo htmlspecialchars($data['jumlah']); ?></td>
                        <td>
                            <a href="edit/edit.php?id=<?php echo $data['id_item']; ?>" class="btn btn-edit">Edit</a>
                            <a href="index.php?action=delete&id=<?php echo $data['id_item']; ?>" class="btn btn-delete" 
                               onclick="showDeleteModal(event, this.href);">Hapus</a>
                        </td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                    <?php
                    endif;
                    ?>
                </tbody>
            </table>

            <div class="table-footer">
                <span class="footer-info">
                    Menampilkan <?php echo $data_di_halaman_ini; ?> dari <?php echo $total_data; ?> data
                </span>

                <?php if ($total_halaman > 1): ?>
                    <nav class="pagination">
                        
                        <a href="<?php echo $link_prefix; ?>halaman=<?php echo $halaman_aktif - 1; ?>"
                           class="page-arrow <?php echo ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">&lt;</a>

                        <a href="#" class="page-number active"><?php echo $halaman_aktif; ?></a>

                        <a href="<?php echo $link_prefix; ?>halaman=<?php echo $halaman_aktif + 1; ?>"
                           class="page-arrow <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">&gt;</a>
                    </nav>
                <?php endif; ?>
            </div>
            
        </div> 

    </div> 
    
    <div id="deleteModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h4>Konfirmasi Hapus</h4>
            <p>Apakah Anda yakin ingin menghapus data ini?</p>
            <div class="modal-buttons">
                <button id="modalCancel" class="btn btn-secondary">Cancel</button>
                <a id="modalConfirm" href="#" class="btn btn-delete">OK</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('kategori').addEventListener('change', function() {
            this.form.submit();
        });

        const modal = document.getElementById('deleteModal');
        const btnCancel = document.getElementById('modalCancel');
        const btnConfirm = document.getElementById('modalConfirm');

        function showDeleteModal(event, deleteUrl) {
            event.preventDefault(); 
            btnConfirm.href = deleteUrl;
            modal.style.display = 'flex';
        }

        btnCancel.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>

</body>
</html>