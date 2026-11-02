# Tugas Pertemuan 7 - Framework Laravel

**Nama:** Aulya Reski  
**Institusi:** STTNF  
**Mata Kuliah:** Framework Laravel

## Repository

https://github.com/0110124254-create/booksales-api-laravel

## File terkait

- `routes/api.php`
- `app/Http/Controllers/SaleController.php`
- `app/Models/Sale.php`
- `app/Models/SaleItem.php`
- `database/migrations/2026_10_31_112413_create_sales_table.php`
- `database/migrations/2026_10_31_112431_create_sale_items_table.php`

## Ringkasan

Project menggunakan model `Sale` dan `SaleItem` untuk merepresentasikan transaksi dan detail buku pada transaksi. Relasi detail transaksi ke transaksi dan buku didefinisikan menggunakan `foreignId` pada migration. Controller transaksi menangani checkout, pengambilan detail berdasarkan referensi, daftar transaksi untuk admin, konfirmasi pembayaran, dan pembatalan transaksi.

Route API terkait terdapat pada `routes/api.php`, sedangkan implementasi proses transaksi berada pada `SaleController`.

## Pengujian

Pengujian fitur transaksi dilakukan melalui test Laravel yang tersedia di `tests/Feature/BookSalesTest.php`. Pengujian mencakup pembuatan transaksi saat checkout, pengurangan stok, penolakan ketika stok tidak cukup, serta proses konfirmasi dan pembatalan oleh admin.

## Catatan kesesuaian instruksi

Implementasi repository saat ini belum sepenuhnya memenuhi pembagian akses CRUD yang tertulis pada instruksi tugas: route checkout dan detail transaksi saat ini bersifat publik, sementara operasi update dan destroy transaksi belum tersedia. Karena itu, bagian tersebut perlu diselesaikan sebelum menyatakan seluruh instruksi CRUD dan autentikasi telah terpenuhi.
