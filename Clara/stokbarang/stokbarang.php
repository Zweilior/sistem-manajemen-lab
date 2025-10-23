<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stok Barang</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="stylestokbarang.css">
</head>
<body>

  <header class="main-header">
    <div class="logo">
      <img src="logoheader.png" alt="Logo" class="logo-img">
      <h1>STOK BARANG</h1>
    </div>
    <nav>
      <a href="#">Beranda</a> | 
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
        <select id="kategori">
          <option value="" selected>Semua Kategori</option>
          <option value="bahan">Bahan</option>
          <option value="alat">Alat</option>
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
          <tr><td colspan="6" style="text-align:center;">(data contoh)</td></tr>
        </tbody>
      </table>

      <div class="table-footer">
        <span class="data-info">Menampilkan 0 dari 0 data</span>
        <div class="pagination">
          <button class="page-arrow" disabled>&lt;</button>
          <button class="page-number active">1</button>
          <button class="page-arrow">&gt;</button>
        </div>
      </div>
    </section>
  </main>

</body>
</html>
