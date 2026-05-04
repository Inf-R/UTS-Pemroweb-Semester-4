<?php
// Konfigurasi database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'uts_perpustakaan_60324073');

// Buat koneksi menggunakan Object-Oriented MySQLi
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset agar mendukung format karakter utf8mb4
$conn->set_charset("utf8mb4");

/**
 * Function helper untuk sanitasi data (mencegah XSS dan SQL Injection dasar)
 * @param mysqli $conn Objek koneksi database
 * @param string $data Data input yang akan disanitasi
 * @return string Data yang sudah bersih
 */
function escape($conn, $data) {
    return htmlspecialchars($conn->real_escape_string(trim($data)), ENT_QUOTES, 'UTF-8');
}
?>