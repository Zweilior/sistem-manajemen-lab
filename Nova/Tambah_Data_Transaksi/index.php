<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Transaksi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="main-header">
        <div class="logo">
            <img src="LogoSamping.png" alt="Logo" class="logo-img">
            <h1>TAMBAH DATA TRANSAKSI</h1>
        </div>
        <nav>
            <a href="#">Beranda</a> |
            <a href="#">Tambah Data Barang</a>
        </nav>
    </header>

    <main>
        <section class="card form-card">
            
            <form class="form-container" action="#" method="POST">
                
                <div class="form-group">
                    <label for="nama-barang">Nama Barang :</label>
                    <input type="text" id="nama-barang" placeholder="Masukkan nama barang...">
                </div>
                
                <div class="form-group">
                    <label for="tanggal-transaksi">Tanggal Transaksi :</label>
                    <input type="text" id="tanggal-transaksi" placeholder="mm / dd / yyyy">
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah :</label>
                    <input type="text" id="jumlah" placeholder="Masukkan dalam bentuk angka">
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan :</label>
                    <input type="text" id="keterangan" placeholder="Tambahkan Keterangan Transaksi...">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        + Tambah Transaksi
                    </button>
                    <button type="button" class="btn btn-secondary">
                        Lihat Data
                    </button>
                </div>

            </form>
        </section>
    </main>

</body>
</html>