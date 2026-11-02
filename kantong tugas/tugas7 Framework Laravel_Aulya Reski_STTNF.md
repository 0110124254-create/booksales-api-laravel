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
- `app/Models/User.php`
- `database/migrations/2026_10_31_112413_create_sales_table.php`
- `database/migrations/2026_10_31_112431_create_sale_items_table.php`
- `database/migrations/2026_11_02_021908_add_user_id_to_sales_table.php`

## Ringkasan

Project menggunakan model `Sale` dan `SaleItem` untuk merepresentasikan transaksi dan detail buku pada transaksi. Relasi detail transaksi ke transaksi dan buku didefinisikan menggunakan `foreignId` pada migration. Migration tambahan menghubungkan transaksi ke akun customer melalui `sales.user_id`, dengan nilai nullable untuk mempertahankan transaksi lama.

Route API terkait terdapat pada `routes/api.php`. Customer yang sudah login dapat membuat transaksi, melihat transaksinya sendiri, dan memperbarui transaksi yang masih pending. Admin dapat melihat seluruh transaksi dan menghapus transaksi. Penghapusan transaksi pending mengembalikan stok buku yang sebelumnya dipesan. Route konfirmasi pembayaran dan pembatalan tetap khusus admin.

## Pengujian

Pengujian fitur transaksi dilakukan melalui test Laravel di `tests/Feature/BookSalesTest.php`, meliputi autentikasi dan role, kepemilikan transaksi, validasi update, kalkulasi total dan stok, penghapusan transaksi oleh admin, serta proses konfirmasi dan pembatalan.

Testing endpoint dapat dilakukan menggunakan Postman sesuai panduan `POSTMAN_TESTING.md`. Guest menerima `401`, role yang tidak sesuai menerima `403`, dan customer yang mencoba mengakses transaksi milik customer lain menerima `404`. Seluruh test Laravel lulus setelah implementasi.
