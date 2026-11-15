# Ruang Baca

Aplikasi toko buku untuk UJK BNSP Junior Web Developer. Laravel menyediakan REST API dan penyimpanan MySQL; React menjadi SPA untuk halaman user dan admin.

## Stack

- PHP 8.3+ dan Laravel
- React, React Router, Axios, dan Vite
- Tailwind CSS
- MySQL/MariaDB
- Laravel Sanctum untuk autentikasi token API

## Instalasi lokal

1. Buat database kosong bernama `bookstore` di MySQL/MariaDB, misalnya lewat phpMyAdmin atau:

   ```sql
   CREATE DATABASE bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Pasang dependensi dan siapkan konfigurasi:

   ```sh
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   ```

   Sesuaikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada `.env` dengan MySQL lokal Anda. Database bawaan pada `.env.example` adalah `bookstore`.

3. Migrasikan dan isi data awal, lalu buat tautan penyimpanan dan aset produksi:

   ```sh
   php artisan migrate --seed
   php artisan storage:link
   npm run build
   ```

4. Jalankan Laravel:

   ```sh
   php artisan serve
   ```

   Untuk pengembangan frontend dengan hot reload, jalankan `npm run dev` di terminal lain.

## Akun demo

Seeder membuat dua akun lokal berikut. Ganti kata sandi sebelum memakai aplikasi di luar lingkungan demo.

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `superadmin@gmail.com` | `password` |
| User | `user@gmail.com` | `password` |

Registrasi dari SPA selalu membuat akun user; peran admin hanya berasal dari akun yang ditetapkan di database/seeder.

## Fitur

- **User:** registrasi dan login, katalog dengan pencarian judul/penulis/ISBN, filter genre, paginasi, detail buku, keranjang lokal, checkout, riwayat pesanan, halaman About, dan formulir kontak admin.
- **Admin:** ringkasan toko, CRUD buku dengan upload sampul, CRUD genre dan penulis, daftar pengguna, pengelolaan pesanan/status pemenuhan, konfirmasi pembayaran, serta kotak masuk pesan.
- **API:** autentikasi Sanctum, katalog dan CRUD terproteksi, checkout/pesanan, kontak, dashboard, serta endpoint manajemen admin. Route admin memerlukan `auth:sanctum` dan `can:access-admin`; route pembeli memakai `can:access-customer`.
- **Database:** relasi buku ke penulis dan genre memakai foreign key. Seeder menyediakan 5 genre, 5 penulis, 15 buku, dan 2 akun demo.

## Batasan yang diketahui

- Checkout SPA saat ini memakai **Bayar di tempat (COD)**. Pesanan dibuat dengan status pembayaran pending; admin mengonfirmasi pembayaran setelah menerima pembayaran, lalu memperbarui status pemenuhan.
- Integrasi pembuatan pembayaran Midtrans Snap belum tersambung ke alur checkout. Endpoint notifikasi Midtrans dan layanan pendukung masih tersedia; pengembangan lanjutan perlu menghubungkan pembuatan transaksi, redirect pembayaran, dan uji sandbox end-to-end.
- Token API disimpan di `localStorage` browser untuk demo SPA ini. Untuk penerapan publik, evaluasi autentikasi cookie Sanctum dan kebijakan keamanan deployment.

## Pengujian

```sh
php artisan test
npm run build
```
