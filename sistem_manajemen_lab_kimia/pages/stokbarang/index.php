<?php
require_once __DIR__ . '/../../config/koneksi.php';                     // Sertakan file koneksi PDO ($pdo)

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 3; 
                                                                        // Siapkan bagian WHERE dari SQL berdasarkan filter pencarian
$whereSql = " WHERE 1=1"; 
$params_sql = []; 
$params_url = []; 

if ($kategori !== '') {                                                 // Filter berdasarkan kategori jika dipilih         
    $whereSql .= " AND k.id_kategori = :kategori";
    $params_sql[':kategori'] = $kategori;
    $params_url['kategori'] = $kategori; 
}

if ($q !== '') {                                                          // Filter pencarian berdasarkan kode atau nama barang
    $whereSql .= " AND (i.id_item = :q_exact OR i.nama_item LIKE :q_like)";
    $params_sql[':q_exact'] = is_numeric($q) ? (int)$q : 0; 
    $params_sql[':q_like']  = '%' . $q . '%'; 
    $params_url['q'] = $q; 
}

try {                                                                      // Hitung total item sesuai filter       
    $countSql = "SELECT COUNT(*) as total
                 FROM stok_lab s
                 JOIN item_alat_bahan i ON s.id_item = i.id_item
                 JOIN kategori_item k ON i.id_kategori = k.id_kategori"
                 . $whereSql; 
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params_sql); 
    $totalItems = (int)$countStmt->fetchColumn();
} catch (Exception $e) {                                                    // Jika query gagal, set totalItems ke 0               
    $totalItems = 0; 
}

$totalPages = $totalItems > 0 ? (int)ceil($totalItems / $perPage) : 1;      // Hitung total halaman
if ($page > $totalPages) $page = $totalPages; 
$offset = ($page - 1) * $perPage;
                                                                            // Siapkan SQL utama untuk mengambil data barang dengan filter & pagination
$sql = "SELECT s.id_stok, i.id_item, i.nama_item, k.id_kategori, k.nama_kategori, i.satuan, s.jumlah
        FROM stok_lab s
        JOIN item_alat_bahan i ON s.id_item = i.id_item
        JOIN kategori_item k ON i.id_kategori = k.id_kategori"
        . $whereSql . 
        " ORDER BY i.nama_item ASC
          LIMIT :limit OFFSET :offset"; 

try {                                                                       // Ambil data barang sesuai filter & pagination
    $stmt = $pdo->prepare($sql);
    foreach ($params_sql as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {                                                    // Jika query gagal, set items ke array kosong
    $items = []; 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>                                                                      <!-- Header HTML umum -->                  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Barang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/stylestokbarang.css">     <!-- Link file CSS khusus halaman stok barang -->
</head>
<body>

    <header class="main-header">
        <div class="logo">                                                  <!-- Logo dan judul aplikasi -->
            <img src="../../assets/img/ikon.png" alt="Logo" class="logo-img">
            <h1>STOK BARANG</h1>
        </div> 
        <nav class="breadcrumbs"> <a href="../dashboard.php">Beranda</a> |          
            <a href="index.php" class="active">Stok Barang</a> </nav>
    </header>

    <main>
        <form method="GET" action="index.php" id="filterForm">
            <section class="card filter-card">                               <!-- Kartu untuk form filter & pencarian -->
                <div class="form-group">                                     <!-- Grup input untuk pencarian -->
                    <label for="search-barang">Search</label>
                    <input type="text" id="search-barang" name="q" placeholder="Kode atau nama barang..."
                           value="<?php echo htmlspecialchars($q); ?>">
                </div>

                <div class="form-group">                                     <!-- Grup input untuk filter kategori -->
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="" <?php echo $kategori === '' ? 'selected' : ''; ?>>Semua Kategori</option>
                        <?php
                        try {
                            $catStmt = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori_item ORDER BY nama_kategori");
                            while ($c = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                                $sel = ($kategori == $c['id_kategori']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($c['id_kategori']) . '" ' . $sel . '>' . htmlspecialchars($c['nama_kategori']) . '</option>';
                            }
                        } catch (Exception $e) {
                        }
                        ?>
                    </select>
                </div>
            </section>
        </form>

        <section class="card table-card">                                     <!-- Kartu untuk tabel data barang -->               
            <table>
                <thead>
                    <tr>                                                      <!-- Header tabel untuk data barang -->
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>                                                       <!-- Body tabel untuk data barang -->
                    <?php if (!empty($items)): ?>
                        <?php
                        $startNo = ($page - 1) * $perPage + 1;
                        foreach ($items as $index => $row): ?>
                            <tr>                                              <!-- Baris data barang -->
                                <td><?php echo $startNo + $index; ?></td>
                                <td><?php echo htmlspecialchars($row['id_item']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_item']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                <td><?php echo htmlspecialchars($row['satuan']); ?></td>
                                <td><?php echo htmlspecialchars($row['jumlah']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>                                              
                        <tr><td colspan="6" style="text-align:center;">(data tidak ditemukan)</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="table-footer">                                          <!-- Footer tabel dengan info data & pagination -->
                <span class="data-info">Menampilkan <?php echo count($items); ?> dari <?php echo $totalItems; ?> data</span>
                <?php if ($totalPages > 1): ?>
                    
                    <nav class="pagination">                                    <!-- Navigasi pagination -->      
                        <?php
                        $queryString = http_build_query($params_url);

                        if ($page > 1):
                            $prev = $page - 1;
                            echo '<a href="?page=' . $prev . '&' . $queryString . '" class="page-arrow">&lt;</a>';
                        else:
                            echo '<span class="page-arrow disabled">&lt;</span>';
                        endif;

                        echo '<span class="page-number active">' . $page . '</span>'; 

                        if ($page < $totalPages):
                            $next = $page + 1;
                            echo '<a href="?page=' . $next . '&' . $queryString . '" class="page-arrow">&gt;</a>';
                        else:
                            echo '<span class="page-arrow disabled">&gt;</span>';
                        endif;
                        ?>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>                                                                   // Auto-submit form saat kategori diubah
        document.getElementById('kategori').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>

</body>
</html>