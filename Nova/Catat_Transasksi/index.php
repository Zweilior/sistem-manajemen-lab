<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="main-header">
        <div class="logo">
    <img src="LogoSamping.png" alt="Logo" class="logo-img">
    <h1>CATAT TRANSAKSI</h1>
</div>
        <nav>
            <a href="#">Beranda</a> |
            <a href="#">Catat Transaksi</a>
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
            
            <button class="btn btn-primary">
                + Tambah Transaksi
            </button>
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
                    </tbody>
            </table>

            <div class="table-footer">
                <span class="data-info">Menampilkan 0 dari 0 data</span> <div class="pagination">
                    <button class="page-arrow" disabled>&lt;</button>
                    <button class="page-number active">1</button>
                    <button class="page-arrow">&gt;</button>
                </div>
            </div>
        </section>
    </main>

</body>
</html>