<?php
// Menghubungkan ke konfigurasi database
require_once 'config/database.php';

// --- A. Validasi ID ---
// Cek apakah parameter 'id' ada di URL dan tidak kosong
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=" . urlencode("Akses ditolak. ID Kategori tidak ditemukan."));
    exit();
}

// Konversi ke integer untuk memastikan keamanan ekstra
$id_kategori = (int)$_GET['id'];

// --- B. Proses Delete ---
// Menggunakan prepared statement untuk mencegah SQL Injection
$query = "DELETE FROM kategori WHERE id_kategori = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_kategori);

if ($stmt->execute()) {
    // Cek affected_rows untuk memastikan ada baris yang benar-benar terhapus
    if ($stmt->affected_rows > 0) {
        // --- C. Redirect jika sukses ---
        header("Location: index.php?pesan=" . urlencode("Data kategori berhasil dihapus."));
    } else {
        // Redirect jika ID tidak ditemukan di database
        header("Location: index.php?error=" . urlencode("Data kategori tidak ditemukan atau sudah dihapus sebelumnya."));
    }
} else {
    // Redirect jika terjadi error pada database (misal: constraint error)
    header("Location: index.php?error=" . urlencode("Gagal menghapus data: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>