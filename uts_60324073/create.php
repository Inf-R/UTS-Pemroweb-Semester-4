<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    // Inisialisasi variabel untuk menyimpan error dan nilai form (agar form tidak reset saat error)
    $errors = [];
    $kode = '';
    $nama = '';
    $deskripsi = '';
    $status = 'Aktif';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil dan sanitasi data dari form menggunakan fungsi helper escape() yang ada di database.php
        $kode = escape($conn, $_POST['kode_kategori'] ?? '');
        $nama = escape($conn, $_POST['nama_kategori'] ?? '');
        $deskripsi = escape($conn, $_POST['deskripsi'] ?? '');
        $status = escape($conn, $_POST['status'] ?? 'Aktif');
        
        // --- Validasi Kode Kategori ---
        if (empty($kode)) {
            $errors[] = "Kode Kategori wajib diisi.";
        } elseif (strlen($kode) < 4 || strlen($kode) > 10) {
            $errors[] = "Kode Kategori harus antara 4-10 karakter.";
        } elseif (substr($kode, 0, 4) !== 'KAT-') {
            $errors[] = "Kode Kategori harus diawali dengan 'KAT-'.";
        } else {
            // Cek duplikasi kode ke database menggunakan prepared statement
            $stmt_cek = $conn->prepare("SELECT kode_kategori FROM kategori WHERE kode_kategori = ?");
            $stmt_cek->bind_param("s", $kode);
            $stmt_cek->execute();
            $stmt_cek->store_result();
            if ($stmt_cek->num_rows > 0) {
                $errors[] = "Kode Kategori sudah terdaftar, gunakan kode lain.";
            }
            $stmt_cek->close();
        }
        
        // --- Validasi Nama Kategori ---
        if (empty($nama)) {
            $errors[] = "Nama Kategori wajib diisi.";
        } elseif (strlen($nama) < 3) {
            $errors[] = "Nama Kategori minimal 3 karakter.";
        } elseif (strlen($nama) > 50) {
            $errors[] = "Nama Kategori maksimal 50 karakter.";
        }
        
        // --- Validasi Deskripsi ---
        if (!empty($deskripsi) && strlen($deskripsi) > 200) {
            $errors[] = "Deskripsi maksimal 200 karakter.";
        }
        
        // --- Validasi Status ---
        if (!in_array($status, ['Aktif', 'Nonaktif'])) {
            $errors[] = "Status tidak valid.";
        }
        
        // Jika tidak ada error, eksekusi proses insert
        if (empty($errors)) {
            $query = "INSERT INTO kategori (kode_kategori, nama_kategori, deskripsi, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $kode, $nama, $deskripsi, $status);
            
            if ($stmt->execute()) {
                // Redirect ke halaman index dengan pesan sukses
                header("Location: index.php?pesan=" . urlencode("Kategori berhasil ditambahkan!"));
                exit();
            } else {
                $errors[] = "Terjadi kesalahan sistem saat menyimpan data: " . $conn->error;
            }
            $stmt->close();
        }
    }
    ?>
    
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Tambah Kategori Baru</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="kode_kategori" class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" value="<?= htmlspecialchars($kode); ?>" placeholder="Contoh: KAT-004" required>
                                <div class="form-text">Format wajib diawali "KAT-" (4-10 karakter).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($nama); ?>" placeholder="Masukkan nama kategori" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Keterangan opsional (maks 200 karakter)"><?= htmlspecialchars($deskripsi); ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status_aktif" value="Aktif" <?= ($status == 'Aktif') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status_aktif">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status_nonaktif" value="Nonaktif" <?= ($status == 'Nonaktif') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status_nonaktif">Nonaktif</label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                                <a href="index.php" class="btn btn-secondary px-4">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>