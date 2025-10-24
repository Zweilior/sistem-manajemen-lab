<?php
// 1. Sertakan koneksi PDO
require_once __DIR__ . '/../../config/koneksi.php'; // Pastikan path ini benar

// 2. Ambil parameter filter dan halaman dari URL
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 3; // Tampilkan 3 data per halaman

// 3. Bangun klausa WHERE dan parameter untuk kueri SQL
$whereSql = " WHERE 1=1"; // Kondisi awal, selalu benar
$params_sql = []; // Parameter untuk PDO execute()
$params_url = []; // Parameter untuk URL pagination

// Tambahkan filter kategori jika dipilih
if ($kategori !== '') {
    $whereSql .= " AND k.id_kategori = :kategori";
    $params_sql[':kategori'] = $kategori;
    $params_url['kategori'] = $kategori; // Tambahkan ke parameter URL
}

// Tambahkan filter search jika ada
if ($q !== '') {
    $whereSql .= " AND (i.id_item = :q_exact OR i.nama_item LIKE :q_like)";
    // Cek jika input search adalah angka (untuk ID)
    $params_sql[':q_exact'] = is_numeric($q) ? (int)$q : 0; // Jika bukan angka, ID 0 tidak akan cocok
    $params_sql[':q_like']  = '%' . $q . '%'; // Untuk pencarian nama
    $params_url['q'] = $q; // Tambahkan ke parameter URL
}

// 4. Hitung total item yang cocok untuk pagination
try {
    $countSql = "SELECT COUNT(*) as total
                 FROM stok_lab s
                 JOIN item_alat_bahan i ON s.id_item = i.id_item
                 JOIN kategori_item k ON i.id_kategori = k.id_kategori"
                 . $whereSql; // Gunakan klausa WHERE yang sama
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params_sql); // Jalankan dengan parameter filter
    $totalItems = (int)$countStmt->fetchColumn();
} catch (Exception $e) {
    $totalItems = 0; // Jika error, anggap tidak ada data
}

// 5. Hitung total halaman dan tentukan offset
$totalPages = $totalItems > 0 ? (int)ceil($totalItems / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages; // Jangan biarkan halaman melebihi total
$offset = ($page - 1) * $perPage;

// 6. Ambil data untuk halaman saat ini
$sql = "SELECT s.id_stok, i.id_item, i.nama_item, k.id_kategori, k.nama_kategori, i.satuan, s.jumlah
        FROM stok_lab s
        JOIN item_alat_bahan i ON s.id_item = i.id_item
        JOIN kategori_item k ON i.id_kategori = k.id_kategori"
        . $whereSql . // Gunakan klausa WHERE yang sama
        " ORDER BY i.nama_item ASC
          LIMIT :limit OFFSET :offset"; // Gunakan placeholder LIMIT dan OFFSET

try {
    $stmt = $pdo->prepare($sql);
    // Bind parameter filter (jika ada)
    foreach ($params_sql as $key => $value) {
         // Tentukan tipe data (int atau string) saat binding
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    // Bind LIMIT dan OFFSET sebagai integer
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC); // Ambil semua data sebagai array asosiatif
} catch (Exception $e) {
    $items = []; // Jika error, tampilkan array kosong
    // error_log("Error fetching data: " . $e->getMessage()); // Catat error (opsional)
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Barang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/stylestokbarang.css">
</head>
<body>

    <header class="main-header">
        <div class="logo">
            <img src="../../assets/img/ikon.png" alt="Logo" class="logo-img">
            <h1>STOK BARANG</h1>
        </div>
        <nav class="breadcrumbs"> <a href="../dashboard.php">Beranda</a> |
            <a href="index.php" class="active">Stok Barang</a> </nav>
    </header>

    <main>
        <form method="GET" action="index.php" id="filterForm">
            <section class="card filter-card">
                <div class="form-group">
                    <label for="search-barang">Search</label>
                    <input type="text" id="search-barang" name="q" placeholder="Kode atau nama barang..."
                           value="<?php echo htmlspecialchars($q); ?>">
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="" <?php echo $kategori === '' ? 'selected' : ''; ?>>Semua Kategori</option>
                        <?php
                        // Ambil daftar kategori untuk opsi select
                        try {
                            $catStmt = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori_item ORDER BY nama_kategori");
                            while ($c = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                                $sel = ($kategori == $c['id_kategori']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($c['id_kategori']) . '" ' . $sel . '>' . htmlspecialchars($c['nama_kategori']) . '</option>';
                            }
                        } catch (Exception $e) {
                            // Jika error, lewati
                        }
                        ?>
                    </select>
                </div>
            </section>
        </form>

        <section class="card table-card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php
                        // Hitung nomor awal untuk halaman ini
                        $startNo = ($page - 1) * $perPage + 1;
                        foreach ($items as $index => $row): ?>
                            <tr>
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

            <div class="table-footer">
                <span class="data-info">Menampilkan <?php echo count($items); ?> dari <?php echo $totalItems; ?> data</span>
                <?php if ($totalPages > 1): // Tampilkan pagination hanya jika lebih dari 1 halaman ?>
                    
                    <nav class="pagination">
                        <?php
                        // Buat query string dari parameter URL saat ini (filter q dan kategori)
                        $queryString = http_build_query($params_url);

                        // 1. Tombol Panah Kiri (<)
                        if ($page > 1):
                            $prev = $page - 1;
                            // Tambahkan queryString ke URL
                            echo '<a href="?page=' . $prev . '&' . $queryString . '" class="page-arrow">&lt;</a>';
                        else:
                            // Tombol disabled jika di halaman pertama
                            echo '<span class="page-arrow disabled">&lt;</span>';
                        endif;

                        // 2. Hanya Nomor Halaman Aktif (tidak bisa diklik)
                        // Class 'active' akan memberi style biru (sesuaikan di CSS jika perlu)
                        echo '<span class="page-number active">' . $page . '</span>'; 

                        // 3. Tombol Panah Kanan (>)
                        if ($page < $totalPages):
                            $next = $page + 1;
                             // Tambahkan queryString ke URL
                            echo '<a href="?page=' . $next . '&' . $queryString . '" class="page-arrow">&gt;</a>';
                        else:
                             // Tombol disabled jika di halaman terakhir
                            echo '<span class="page-arrow disabled">&gt;</span>';
                        endif;
                        ?>
                    </nav>
                    <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        document.getElementById('kategori').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>

</body>
</html>