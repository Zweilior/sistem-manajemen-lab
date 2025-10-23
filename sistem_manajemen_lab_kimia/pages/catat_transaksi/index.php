<?php
// Sertakan koneksi PDO
include __DIR__ . '/../../config/koneksi.php';

// --- LOGIKA PAGINATION ---
$jumlah_data_per_halaman = 3;

$sql_where = "";
$join_clause = "LEFT JOIN stok_lab s ON t.id_stok = s.id_stok 
                LEFT JOIN item_alat_bahan i ON s.id_item = i.id_item";
$params_url = [];
$params_sql = [];

// Filter berdasarkan pencarian tanggal
if (!empty($_GET['search-tanggal'])) {
    $tanggal = $_GET['search-tanggal'];
    $sql_where .= " WHERE DATE(t.tanggal_transaksi) = ? ";
    $params_sql[] = $tanggal;
    $params_url[] = "search-tanggal=" . urlencode($tanggal);
}

// Filter berdasarkan keterangan (masuk/keluar)
if (!empty($_GET['search-keterangan']) && in_array($_GET['search-keterangan'], ['masuk', 'keluar'])) {
    $keterangan = $_GET['search-keterangan'];
    $sql_where .= $sql_where ? " AND " : " WHERE ";
    $sql_where .= " t.jenis_transaksi = ? ";
    $params_sql[] = ucfirst($keterangan); // Mengubah 'masuk' menjadi 'Masuk'
    $params_url[] = "search-keterangan=$keterangan";
}

$query_string = implode("&", $params_url);
$link_prefix = $query_string ? "?$query_string&" : "?";

// Hitung total data
$stmt_total = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM transaksi_stok t 
    $join_clause 
    $sql_where
");
$stmt_total->execute($params_sql);
$total_data = $stmt_total->fetchColumn();

// Hitung total halaman
$total_halaman = ceil($total_data / $jumlah_data_per_halaman);
if ($total_halaman < 1) $total_halaman = 1;

// Tentukan halaman aktif
$halaman_aktif = (isset($_GET['halaman'])) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;
if ($halaman_aktif > $total_halaman) $halaman_aktif = $total_halaman;

$data_awal = ($halaman_aktif - 1) * $jumlah_data_per_halaman;

// Query untuk mengambil data transaksi
$sql_data = "
    SELECT t.id_transaksi, t.id_stok, i.nama_item, 
           t.tanggal_transaksi, t.jumlah, t.jenis_transaksi
    FROM transaksi_stok t 
    $join_clause 
    $sql_where 
    ORDER BY t.tanggal_transaksi DESC 
    LIMIT $data_awal, $jumlah_data_per_halaman
";

$stmt_data = $pdo->prepare($sql_data);
$stmt_data->execute($params_sql);
$data_di_halaman_ini = $stmt_data->rowCount();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/catat_transaksi.css">
</head>
<body>

    <header class="main-header">
        <div class="logo">
    <img src="../../assets/img/ikon.png" alt="Logo" class="logo-img">
    <h1>CATAT TRANSAKSI</h1>
</div>
        <nav>
            <a href="../dashboard.php">Beranda</a> |
            <a href="#" class="active">Catat Transaksi</a>
        </nav>
    </header>

    <main>
        <section class="card filter-card">
            <div class="form-group">
                <label for="search-tanggal">Search</label>
                <input type="text" id="search-tanggal" placeholder="Tanggal transaksi">
            </div>
            
            <div class="form-group">
                <label for="search-keterangan">Keterangan</label>
                <select id="search-keterangan">
                    <option value="" disabled selected>Search</option>
                    <option value="masuk">Pemasukan</option>
                    <option value="keluar">Pengeluaran</option>
                </select>
            </div>
            
            <a href="add.php" class="btn btn-primary">
                + Tambah Transaksi
            </a>
        </section>

        <section class="card table-card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID_Transaksi</th>
                        <th>ID_Stok</th>
                        <th>Nama Barang</th>
                        <th>Tanggal Transaksi</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($data_di_halaman_ini > 0):
                        $nomor = $data_awal + 1;
                        while ($row = $stmt_data->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?php echo $nomor++; ?></td>
                        <td><?php echo htmlspecialchars($row['id_transaksi']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_stok']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_item']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                        <td><?php echo htmlspecialchars($row['jumlah']); ?></td>
                        <td><?php echo htmlspecialchars($row['jenis_transaksi']); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Tidak ada data transaksi.</td>
                    </tr>
                    <?php
                    endif;
                    ?>
                </tbody>
            </table>

            <div class="table-footer">
                <span class="data-info">
                    Menampilkan <?php echo $data_di_halaman_ini; ?> dari <?php echo $total_data; ?> data
                </span>
                
                <?php if ($total_halaman > 1): ?>
                <div class="pagination">
                    <button class="page-arrow <?php echo ($halaman_aktif <= 1) ? 'disabled' : ''; ?>" 
                            onclick="window.location.href='<?php echo $link_prefix; ?>halaman=<?php echo $halaman_aktif - 1; ?>'"
                            <?php echo ($halaman_aktif <= 1) ? 'disabled' : ''; ?>>&lt;</button>
                    
                    <button class="page-number active"><?php echo $halaman_aktif; ?></button>
                    
                    <button class="page-arrow <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>"
                            onclick="window.location.href='<?php echo $link_prefix; ?>halaman=<?php echo $halaman_aktif + 1; ?>'"
                            <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>>&gt;</button>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
    document.getElementById('search-keterangan').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    </script>

</body>
</html>