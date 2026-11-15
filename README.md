# BookStore — Ruang Baca

Aplikasi toko buku berbasis Laravel dengan dua peran: admin mengelola katalog, kategori, user, pesan, dan pesanan; user dapat mencari buku, mendaftar, mengelola keranjang, dan membayar melalui Midtrans Snap.

## Menjalankan aplikasi (Windows)

```powershell
composer install
npm.cmd install
npm.cmd run build
Copy-Item .env.example .env
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan key:generate
php artisan migrate --seed
php artisan app:create-admin
php artisan serve
```

Jika `.env` sudah ada, jangan menyalinnya kembali. Admin masuk melalui `/login`; user dapat membuat akun melalui `/register`. Dashboard admin tersedia di `/admin`.

## Midtrans Snap

Isi Sandbox Server Key dari Midtrans Dashboard di `.env`. Integrasi redirect checkout ini menggunakan Server Key di backend; Client Key belum diperlukan karena halaman pembayaran di-host Midtrans.

```dotenv
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_IS_PRODUCTION=false
```

Gunakan kunci sandbox saat pengembangan. Atur URL Payment Notification Midtrans menjadi `{APP_URL}/api/midtrans/notification`. Server key hanya dipakai backend. Jika kunci belum diisi, checkout menampilkan pesan konfigurasi dan tidak membuat transaksi.

## Fitur

- **User:** registrasi/login, pencarian buku, keranjang, checkout, pembayaran online, riwayat pesanan, About Us, dan formulir kontak.
- **Admin:** kelola buku dan kategori, daftar user, pantau pesanan serta status pengiriman, dan baca pesan pengguna.
- **API:** katalog dan transaksi tersedia melalui endpoint `/api`; endpoint tulis yang terlindungi menggunakan Sanctum.

Pesanan menyimpan snapshot judul serta harga item. Stok dipesan saat checkout; notifikasi Midtrans yang terverifikasi mengonfirmasi pembayaran dan status pembayaran kedaluwarsa mengembalikan stok.

Foto hero buku: [Prateek Katyal di Unsplash](https://unsplash.com/photos/stack-of-books-on-brown-wooden-shelf-Tl0lYXcyT-A). Sampul kartu produk menggunakan artwork lokal per judul.
