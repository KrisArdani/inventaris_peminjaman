<?php
require 'koneksi.php';
try {
    $koneksi->exec("ALTER TABLE peminjaman ADD COLUMN nama_kegiatan VARCHAR(255) NULL");
    $koneksi->exec("ALTER TABLE peminjaman ADD COLUMN tujuan TEXT NULL");
    $koneksi->exec("ALTER TABLE peminjaman ADD COLUMN lokasi VARCHAR(255) NULL");
    echo "Columns added successfully.\n";
} catch(PDOException $e) {
    echo "Error or columns already exist: " . $e->getMessage() . "\n";
}
