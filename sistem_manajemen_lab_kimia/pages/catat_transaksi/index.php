<?php
include __DIR__ . '/../../config/koneksi.php';                       // Sertakan file koneksi PDO ($pdo)      

$jumlah_data_per_halaman = 3;                                       // Jumlah data transaksi per halaman                        

$sql_where = " WHERE 1=1 ";
$join_clause = "LEFT JOIN stok_lab s ON t.id_stok = s.id_stok
                LEFT JOIN item_alat_bahan i ON s.id_item = i.id_item";
$params_url = [];
$params_sql = [];

if (!empty($_GET['search-tanggal'])) {                                  // Validasi dan proses input tanggal
    $tanggal_input = $_GET['search-tanggal']; 

    $date_obj = DateTime::createFromFormat('d/m/Y', $tanggal_input);

    if ($date_obj !== false) {                                          // Jika format tanggal valid
        $tanggal_sql = $date_obj->format('Y-m-d'); 

        $sql_where .= " AND DATE(t.tanggal_transaksi) = :tanggal ";
        $params_sql[':tanggal'] = $tanggal_sql;
        $params_url['search-tanggal'] = $tanggal_input;
    } else {                                                            // Jika format tanggal tidak valid, tetap simpan input untuk URL
        $params_url['search-tanggal'] = $tanggal_input; 
    }
}

if (!empty($_GET['search-keterangan']) && in_array($_GET['search-keterangan'], ['masuk', 'keluar'])) { // Validasi input keterangan
    $keterangan = $_GET['search-keterangan'];
    $jenis_transaksi = ucfirst($keterangan);
    $sql_where .= " AND t.jenis_transaksi = :jenis_transaksi ";
    $params_sql[':jenis_transaksi'] = $jenis_transaksi;
    $params_url['search-keterangan'] = $keterangan;
}

$query_string = http_build_query($params_url);                          // Bangun query string untuk pagination links
$link_prefix = $query_string ? "?$query_string&" : "?";

try {                                                                    // Mempersiapkan query total data transaksi dengan filter                      
    $stmt_total = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM transaksi_stok t
        $join_clause
        $sql_where
    ");
    $stmt_total->execute($params_sql);
    $total_data = $stmt_total->fetchColumn();
} catch (PDOException $e) {                                                // Menangkap error query total data transaksi
    $total_data = 0;
}


$total_halaman = ceil($total_data / $jumlah_data_per_halaman);              // Hitung total halaman
if ($total_halaman < 1) $total_halaman = 1;

$halaman_aktif = (isset($_GET['halaman'])) ? (int)$_GET['halaman'] : 1;     // Ambil nomor halaman dari URL
if ($halaman_aktif < 1) $halaman_aktif = 1;
if ($halaman_aktif > $total_halaman) $halaman_aktif = $total_halaman;

$data_awal = ($halaman_aktif - 1) * $jumlah_data_per_halaman;               // Hitung offset data awal

$data_transaksi = [];                                                       // Inisialisasi array data transaksi kosong
$data_di_halaman_ini = 0;

try {                                                                       // Mempersiapkan query data transaksi dengan filter dan pagination
    $sql_data = "
        SELECT t.id_transaksi, t.id_stok, i.nama_item,
               t.tanggal_transaksi, t.jumlah, t.jenis_transaksi
        FROM transaksi_stok t
        $join_clause
        $sql_where
        ORDER BY t.tanggal_transaksi DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt_data = $pdo->prepare($sql_data);                                                  // Siapkan statement query data transaksi

    foreach ($params_sql as $key => $value) {                                               // Bind parameter filter (jika ada)
        $stmt_data->bindValue($key, $value);
    }
    $stmt_data->bindValue(':limit', $jumlah_data_per_halaman, PDO::PARAM_INT);              // Bind nilai LIMIT sebagai integer
    $stmt_data->bindValue(':offset', $data_awal, PDO::PARAM_INT);

    $stmt_data->execute();                                                                  // Menjalankan query data transaksi
    $data_transaksi = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
    $data_di_halaman_ini = count($data_transaksi);

} catch (PDOException $e) {                                                                 // Menangkap error query data transaksi
     error_log("Error fetching transactions: " . $e->getMessage());
}
?>
                                                                                            
<!DOCTYPE html>                                                                             <!-- Deklarasi tipe dokumen HTML5 -->            
<html lang="id">
<head>                                                                                      <!-- Mulai tag html dengan bahasa Indonesia -->
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/catat_transaksi.css">                     <!-- Link file CSS khusus halaman catat transaksi -->
</head>
<body>

    <header class="main-header">                                                            <!-- Header utama halaman --->      
        <div class="logo">                                                                  <!-- Logo dan judul halaman --->             
            <img src="../../assets/img/ikon.png" alt="Logo" class="logo-img">
            <h1>CATAT TRANSAKSI</h1>
        </div>
        <nav class="breadcrumbs">                                                           <!-- Breadcrumbs navigasi --->
            <a href="../dashboard.php">Beranda</a> |
            <a href="index.php"class="active">Catat Transaksi</a>
        </nav>
    </header>

    <main>
        <form method="GET" action="index.php" id="filterFormTransaksi">                     <!-- Form untuk filter transaksi --->        
            <section class="card filter-card">                                              <!-- Kartu/form untuk filter transaksi --->                          
                <div class="form-group">                                                    <!-- Grup input untuk tanggal transaksi --->                         
                    <label for="search-tanggal">Search</label>
                    <input type="text" id="search-tanggal" name="search-tanggal" placeholder="dd/mm/yyyy"
                           value="<?php echo isset($_GET['search-tanggal']) ? htmlspecialchars($_GET['search-tanggal']) : ''; ?>">
                </div>

                <div class="form-group">                                                    <!-- Grup input untuk keterangan transaksi --->
                    <label for="search-keterangan">Keterangan</label>
                    <select id="search-keterangan" name="search-keterangan">
                        <option value="" <?php echo (isset($_GET['search-keterangan']) && $_GET['search-keterangan'] == '') ? 'selected' : ''; ?>>Semua</option>
                        <option value="masuk" <?php echo (isset($_GET['search-keterangan']) && $_GET['search-keterangan'] == 'masuk') ? 'selected' : ''; ?>>Pemasukan</option>
                        <option value="keluar" <?php echo (isset($_GET['search-keterangan']) && $_GET['search-keterangan'] == 'keluar') ? 'selected' : ''; ?>>Pengeluaran</option>
                    </select>
                </div>

                <a href="add.php" class="btn btn-primary" style="margin-left: auto;">       <!-- Tombol untuk tambah data transaksi --->
                    + Tambah Transaksi
                </a>
            </section>
        </form>

        <section class="card table-card">                                                   <!-- Kartu untuk tabel transaksi --->                            
            <table>
                <thead>
                    <tr>                                                                    <!-- Header tabel transaksi --->                                
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
                    <?php                                                                   /// Loop untuk menampilkan data transaksi --->
                    if ($data_di_halaman_ini > 0):
                        $nomor = $data_awal + 1;
                        foreach ($data_transaksi as $row):
                    ?>
                    <tr>                                                                                    <!-- Menampilkan baris data transaksi --->
                        <td><?php echo $nomor++; ?></td>
                        <td><?php echo htmlspecialchars($row['id_transaksi']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_stok']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_item'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                        <td><?php echo htmlspecialchars($row['jumlah']); ?></td>
                        <td><?php echo htmlspecialchars($row['jenis_transaksi']); ?></td>
                    </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Tidak ada data transaksi ditemukan.</td>       <!-- Tampilkan pesan jika tidak ada data --->
                    </tr>
                    <?php
                    endif;
                    ?>
                </tbody>
            </table>

            <div class="table-footer">                                                               <!-- Footer tabel dengan info data & pagination --->
                <span class="data-info">
                    Menampilkan <?php echo $data_di_halaman_ini; ?> dari <?php echo $total_data; ?> data
                </span>

                <?php if ($total_halaman > 1): ?>
                <nav class="pagination">                                                                                    <!-- Navigasi pagination --->
                    <?php $queryString = http_build_query($params_url); ?>
                     <a href="<?php echo $link_prefix; ?>halaman=<?php echo $halaman_aktif - 1; ?>"         
                        class="page-arrow <?php echo ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">&lt;</a>                  <!-- Tombol panah kiri --->
                     <span class="page-number active"><?php echo $halaman_aktif; ?></span>
                     <a href="<?php echo $link_prefix; ?>halaman=<?php echo $halaman_aktif + 1; ?>"
                        class="page-arrow <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">&gt;</a>     <!-- Tombol panah kanan --->
                 </nav>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>                                                        // Script untuk submit form filter otomatis
        document.getElementById('search-keterangan').addEventListener('change', function() {
            document.getElementById('filterFormTransaksi').submit();
        });

        document.getElementById('filterFormTransaksi').addEventListener('submit', function(event) { 
        });
    </script>

</body>
</html>