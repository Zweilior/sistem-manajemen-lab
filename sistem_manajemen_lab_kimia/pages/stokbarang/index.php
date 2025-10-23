<?php
// ...existing code...
// tambahkan koneksi dan logika query sebelum output HTML
require_once __DIR__ . '/../../config/koneksi.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 3; // tampilkan 3 data per halaman

// konstruk WHERE bagian yang sama untuk COUNT dan SELECT
$whereSql = " WHERE 1=1";
$params = [];

if ($kategori !== '') {
    $whereSql .= " AND k.id_kategori = :kategori";
    $params[':kategori'] = $kategori;
}

if ($q !== '') {
    $whereSql .= " AND (i.id_item = :q_exact OR i.nama_item LIKE :q_like)";
    $params[':q_exact'] = is_numeric($q) ? (int)$q : 0;
    $params[':q_like']  = '%' . $q . '%';
}

// hitung total item untuk pagination
try {
    $countSql = "SELECT COUNT(*) as total
                 FROM stok_lab s
                 JOIN item_alat_bahan i ON s.id_item = i.id_item
                 JOIN kategori_item k ON i.id_kategori = k.id_kategori"
                 . $whereSql;
    $countStmt = $pdo->prepare($countSql);

    // bind nilai filter untuk count
    foreach ($params as $k => $v) {
        $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalItems = (int)$countStmt->fetchColumn();
} catch (Exception $e) {
    $totalItems = 0;
}

$totalPages = $totalItems > 0 ? (int)ceil($totalItems / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// ambil data halaman saat ini
$sql = "SELECT s.id_stok, i.id_item, i.nama_item, k.id_kategori, k.nama_kategori, i.satuan, s.jumlah
        FROM stok_lab s
        JOIN item_alat_bahan i ON s.id_item = i.id_item
        JOIN kategori_item k ON i.id_kategori = k.id_kategori"
        . $whereSql .
        " ORDER BY i.nama_item ASC
          LIMIT :limit OFFSET :offset";

try {
    $stmt = $pdo->prepare($sql);
    // bind filter params
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    // bind limit/offset sebagai integer
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $items = [];
    // error_log($e->getMessage());
}
// ...existing code...
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
    <nav>
      <a href="../dashboard.php">Beranda</a> |
      <a href="#" class="active">Stok Barang</a>
    </nav>
  </header>

  <main>
    <!-- FILTER CARD -->
    <section class="card filter-card">
      <div class="form-group">
        <label for="search-barang">Search</label>
        <input type="text" id="search-barang" placeholder="Kode atau nama barang...">
      </div>

      <div class="form-group">
          <label for="kategori">Kategori</label>
          <select id="kategori" name="kategori">
            <option value="" <?php echo $kategori === '' ? 'selected' : ''; ?>>Semua Kategori</option>
            <?php
            // ambil daftar kategori untuk opsi select
            try {
                $catStmt = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori_item ORDER BY nama_kategori");
                while ($c = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                    $sel = ($kategori == $c['id_kategori']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($c['id_kategori']) . '" ' . $sel . '>' . htmlspecialchars($c['nama_kategori']) . '</option>';
                }
            } catch (Exception $e) {
                // jika error, lewati
            }
            ?>
          </select>
      </div>
    </section>

    <!-- TABLE CARD -->
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
            // hitung nomor awal untuk halaman ini
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
        <span class="data-info">Menampilkan <?php echo count($items); ?> data</span>
            <nav class="pagination">
              <?php
              // 1. Panah « (jika bukan halaman 1)
              if ($page > 1):
                  $prev = $page - 1;
                  echo '<a href="?page=' . $prev . '&q=' . urlencode($q) . '&kategori=' . urlencode($kategori) . '" class="page-arrow">&lt;</a>';
              else:
                  echo '<span class="page-arrow disabled">&lt;</span>';
              endif;

              // 2. Nomor halaman (tampilkan 5 halaman di sekitar aktif)
              $range = 2;
              $start = max(1, $page - $range);
              $end   = min($totalPages, $page + $range);

              for ($i = $start; $i <= $end; $i++):
                $active = ($i === $page) ? 'active' : '';
                echo '<a href="?page=' . $i . '&q=' . urlencode($q) . '&kategori=' . urlencode($kategori) . '" class="page-number ' . $active . '">' . $i . '</a>';
              endfor;

              // 3. Panah » (jika bukan halaman terakhir)
              if ($page < $totalPages):
                $next = $page + 1;
                echo '<a href="?page=' . $next . '&q=' . urlencode($q) . '&kategori=' . urlencode($kategori) . '" class="page-arrow">&gt;</a>';
              else:
                echo '<span class="page-arrow disabled">&gt;</span>';
              endif;
              ?>
            </nav>
      </div>
    </section>
  </main>

</body>
</html>