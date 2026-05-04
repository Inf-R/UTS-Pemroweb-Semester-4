<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    // --- 1. Ambil dan Validasi ID dari GET ---
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: index.php?error=" . urlencode("ID Kategori tidak ditemukan."));
        exit();
    }
    
    $id_kategori = (int)$_GET['id'];
    
    // --- 2. Retrieve Data Berdasarkan ID ---
    $stmt_get = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
    $stmt_get->bind_param("i", $id_kategori);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: index.php?error=" . urlencode("Data kategori tidak ditemukan di database."));
        exit();
    }
    
    $data_kategori = $result->fetch_assoc();
    $stmt_get->close();
    
    // Inisialisasi variabel form dengan data dari database
    $errors = [];
    $kode = $data_kategori['kode_kategori'];
    $nama = $data_kategori['nama_kategori'];
    $deskripsi = $data_kategori['deskripsi'];
    $status = $data_kategori['status'];
    
    // --- 3. Proses Update Jika Ada Request POST ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $kode = escape($conn, $_POST['kode_kategori'] ?? '');
        $nama = escape($conn, $_POST['nama_kategori'] ?? '');
        $deskripsi = escape($conn, $_POST['deskripsi'] ?? '');
        $status = escape($conn, $_POST['status'] ?? 'Aktif');
        
        // Validasi Kode Kategori
        if (empty($kode)) {
            $errors[] = "Kode Kategori wajib diisi.";
        } elseif (strlen($kode) < 4 || strlen($kode) > 10) {
            $errors[] = "Kode Kategori harus antara 4-10 karakter.";
        } elseif (substr($kode, 0, 4) !== 'KAT-') {
            $errors[] = "Kode Kategori harus diawali dengan 'KAT-'.";
        } else {
            // Cek duplikasi kode, EXCLUDE id_kategori yang sedang diedit
            $stmt_cek = $conn->prepare("SELECT kode_kategori FROM kategori WHERE kode_kategori = ? AND id_kategori != ?");
            $stmt_cek->bind_param("si", $kode, $id_kategori);
            $stmt_cek->execute();
            $stmt_cek->store_result();
            if ($stmt_cek->num_rows > 0) {
                $errors[] = "Kode Kategori sudah dipakai oleh kategori lain.";
            }
            $stmt_cek->close();
        }
        
        // Validasi Nama Kategori
        if (empty($nama)) {
            $errors[] = "Nama Kategori wajib diisi.";
        } elseif (strlen($nama) < 3) {
            $errors[] = "Nama Kategori minimal 3 karakter.";
        } elseif (strlen($nama) > 50) {
            $errors[] = "Nama Kategori maksimal 50 karakter.";
        }
        
        // Validasi Deskripsi
        if (!empty($deskripsi) && strlen($deskripsi) > 200) {
            $errors[] = "Deskripsi maksimal 200 karakter.";
        }
        
        // Validasi Status
        if (!in_array($status, ['Aktif', 'Nonaktif'])) {
            $errors[] = "Status tidak valid.";
        }
        
        // Jika tidak ada error, eksekusi proses UPDATE
        if (empty($errors)) {
            $query_update = "UPDATE kategori SET kode_kategori = ?, nama_kategori = ?, deskripsi = ?, status = ? WHERE id_kategori = ?";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bind_param("ssssi", $kode, $nama, $deskripsi, $status, $id_kategori);
            
            if ($stmt_update->execute()) {
                header("Location: index.php?pesan=" . urlencode("Data Kategori berhasil diperbarui!"));
                exit();
            } else {
                $errors[] = "Terjadi kesalahan sistem: " . $conn->error;
            }
            $stmt_update->close();
        }
    }
    ?>
    
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Edit Kategori</h4>
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
                                <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" value="<?= htmlspecialchars($kode); ?>" required>
                                <div class="form-text">Format wajib diawali "KAT-" (4-10 karakter).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($nama); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($deskripsi); ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status_aktif" value="Aktif" <?= ($status === 'Aktif') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status_aktif">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status_nonaktif" value="Nonaktif" <?= ($status === 'Nonaktif') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status_nonaktif">Nonaktif</label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning px-4">Update Data</button>
                                <a href="index.php" class="btn btn-secondary px-4">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>