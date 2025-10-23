<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    
    include __DIR__ . '/koneksi.php'; 

    $id_item     = (int)$_POST['id_item'];
    $nama_item   = mysqli_real_escape_string($koneksi, $_POST['nama_item']);
    $id_kategori = (int)$_POST['id_kategori'];
    $satuan      = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    $jumlah      = (int)$_POST['jumlah'];

    $query1 = "UPDATE item_alat_bahan SET 
                   nama_item = '$nama_item',
                   id_kategori = $id_kategori,
                   satuan = '$satuan'
               WHERE id_item = $id_item";
    
    $query2 = "UPDATE stok_lab SET
                   jumlah = $jumlah,
                   tanggal_update = NOW()
               WHERE id_item = $id_item";
    
    mysqli_query($koneksi, $query1);
    mysqli_query($koneksi, $query2);

    header('Location: index.php');
    exit;
}
?>
<?php
include __DIR__ . '/koneksi.php'; 

$jumlah_data_per_halaman = 3; 

$where_clause = "";
$join_clause = " LEFT JOIN kategori_item k ON iab.id_kategori = k.id_kategori
                 LEFT JOIN stok_lab s ON iab.id_item = s.id_item "; 
$params = []; 

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
    $where_clause .= " WHERE (iab.id_item LIKE '%$search%' OR iab.nama_item LIKE '%$search%') ";
    $params[] = "search=" . urlencode($search);
}

if (!empty($_GET['kategori']) && $_GET['kategori'] != 'semua') { 
    $kategori_id = (int)$_GET['kategori'];
    $where_clause .= ($where_clause == "") ? " WHERE " : " AND ";
    $where_clause .= " iab.id_kategori = $kategori_id "; 
    $params[] = "kategori=$kategori_id";
}

$query_string = implode("&", $params);
$link_prefix = $query_string ? "?$query_string&" : "?";

$query_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM item_alat_bahan iab $join_clause $where_clause");
$data_total_row = mysqli_fetch_assoc($query_total);
$total_data = $data_total_row['total'];

$total_halaman = ceil($total_data / $jumlah_data_per_halaman);
if ($total_halaman < 1) $total_halaman = 1; 

$halaman_aktif = (isset($_GET['halaman'])) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;
if ($halaman_aktif > $total_halaman) $halaman_aktif = $total_halaman;

$data_awal = ($halaman_aktif - 1) * $jumlah_data_per_halaman;

$query_data = mysqli_query($koneksi, "SELECT iab.id_item, iab.nama_item, iab.satuan, k.nama_kategori, s.jumlah 
                                      FROM item_alat_bahan iab 
                                      $join_clause 
                                      $where_clause 
                                      ORDER BY iab.id_item ASC 
                                      LIMIT $data_awal, $jumlah_data_per_halaman");

$data_di_halaman_ini = mysqli_num_rows($query_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Laboratorium</title>
    <link rel="stylesheet" href="keloladata.css">
    
    <style>
        .filter-card .btn-primary { 
            margin-left: auto; 
            align-self: flex-end; 
        }
    </style>
</head>
<body>

    <div class="container">
        
        <header class="header">
            <div class="header-title">
                <img src="./img/ikon.png" alt="Ikon Lab" class="header-icon">
                <h1>KELOLA DATA</h1>
            </div>
            <nav class="breadcrumbs">
                Beranda | Kelola Data
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
            
            <a href="tambah_barang.php" class="btn btn-primary">+ Tambah Barang</a>
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
                        
                        while ($data = mysqli_fetch_assoc($query_data)):
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
                            <a href="hapus.php?id=<?php echo $data['id_item']; ?>" class="btn btn-delete" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Hapus</a>
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
    
    <script>
        document.getElementById('kategori').addEventListener('change', function() {
            this.form.submit();
        });
    </script>

</body>
</html>