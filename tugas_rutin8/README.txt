TUGAS RUTIN 8 - CRUD INVENTARIS PRODUK

1. DESKRIPSI
Proyek ini merupakan aplikasi web sederhana untuk mengelola data inventaris produk menggunakan PHP, MySQL, PDO, dan HTML/CSS.

2. FITUR APLIKASI
- Menampilkan daftar produk.
- Menambahkan produk baru.
- Mengubah data produk.
- Menghapus produk dengan halaman konfirmasi.
- Mencari produk berdasarkan nama produk, kategori, atau supplier.
- Menampilkan data dengan pagination.
- Menggunakan PDO untuk koneksi database.
- Menggunakan prepared statement untuk query.
- Menampilkan notifikasi setelah operasi berhasil.

3. TEKNOLOGI
- PHP
- MySQL
- PDO
- HTML
- CSS
- XAMPP
- phpMyAdmin

4. PERSIAPAN
- Instal dan jalankan XAMPP.
- Aktifkan Apache dan MySQL.
- Buat database bernama inventaris_db.
- Pastikan tabel produk, kategori, dan supplier tersedia.
- Sesuaikan konfigurasi database pada config/database.php.

5. CARA MENJALANKAN
- Letakkan folder TUGAS_RUTIN8 di dalam C:\xampp\htdocs\.
- Jalankan Apache dan MySQL melalui XAMPP Control Panel.
- Buka browser.
- Akses alamat berikut:

  http://localhost/TUGAS_RUTIN8/index.php

6. STRUKTUR FILE
- config/database.php : konfigurasi dan koneksi database.
- index.php           : halaman daftar, pencarian, dan pagination.
- create.php          : halaman tambah produk.
- edit.php            : halaman edit produk.
- delete.php          : halaman konfirmasi dan penghapusan produk.
- test_connection.php : file untuk menguji koneksi database.
- README.txt          : dokumentasi proyek.

7. CATATAN
Pastikan nama database, tabel, dan kolom sesuai dengan konfigurasi aplikasi. File database.php mengatur koneksi ke database MySQL.

Proyek ini dibuat untuk memenuhi tugas praktikum pemrograman web dan pengelolaan database.
