# Panduan Singkat Pakai Website Terra Kala

Website ini adalah toko online sederhana yang sudah dilengkapi fitur belanja, keranjang, checkout, login admin, dan login seller.

## 1. Persiapan di komputer
Sebelum buka website, pastikan Anda sudah menginstal XAMPP dan menjalankannya.

1. Install XAMPP.
2. Buka XAMPP Control Panel.
3. Nyalakan modul:
   - Apache
   - MySQL

## 2. Tempatkan project di folder XAMPP
Salin folder project ini ke folder berikut:

- C:\xampp\htdocs\onlinestore

Jika folder Anda berbeda nama, sesuaikan saja nama foldernya saat membuka browser.

## 3. Siapkan database
Database perlu diimpor supaya website bisa berjalan.

1. Buka browser lalu masuk ke:
   - http://localhost/phpmyadmin
2. Buat database baru dengan nama:
   - simple_store
3. Setelah database dibuat, import file:
   - [db.sql](db.sql)

Jika Anda sudah membuat database dengan nama lain, silakan edit pengaturan database di [config.php](config.php).

## 4. Buka website
Setelah database siap, buka URL berikut:

- http://localhost/onlinestore/index.php

Jika Anda menaruh project di folder lain, ganti bagian folder sesuai nama folder Anda.

## 5. Cara belanja
1. Buka halaman utama.
2. Pilih produk yang ingin dibeli.
3. Pilih ukuran produk.
4. Klik tombol tambah ke keranjang.
5. Buka keranjang lalu lanjutkan ke checkout.
6. Isi data pengiriman.
7. Klik proses pesanan.

## 6. Login admin
Untuk masuk ke panel admin:

- http://localhost/onlinestore/admin/login.php

Default login admin:
- Username: admin
- Password: admin123

Setelah login, Anda bisa mengelola produk dan seller.

## 7. Login seller
Untuk mencoba akun seller:

- http://localhost/onlinestore/seller_login.php

Akun demo seller:
- Username: sellerdemo
- Password: seller123

Dengan akun seller, Anda bisa menambah, mengedit, dan menghapus produk sendiri.

## 8. Jika ada masalah
Coba cek hal-hal berikut:

- Apache dan MySQL sudah aktif belum
- Folder project ada di C:\xampp\htdocs
- Database sudah diimport ke simple_store
- Nama folder di URL sesuai dengan folder yang dipakai

Kalau masih error, coba refresh browser atau restart Apache/MySQL dari XAMPP.

## 9. Catatan penting
Password default ini cocok untuk kebutuhan uji coba. Untuk website yang dipakai nyata, sebaiknya ganti password dan tambahkan keamanan tambahan.
