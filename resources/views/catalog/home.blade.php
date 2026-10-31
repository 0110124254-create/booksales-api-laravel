@extends('layouts.catalog')

@section('title', 'Katalog Buku')

@section('content')
    <p class="eyebrow">Book Sales</p>
    <h1>Katalog Buku</h1>
    <p class="intro">Jelajahi koleksi buku, pilih jumlah yang ingin dipesan, lalu tentukan pembayaran tunai atau transfer.</p>

    <div class="catalog-grid">
        <div class="catalog-link">
            <strong>Daftar Genre</strong>
            <span>Lihat lima kategori buku beserta deskripsinya.</span>
            <p><a href="{{ route('genres.index') }}">Lihat genre</a></p>
        </div>
        <div class="catalog-link">
            <strong>Daftar Author</strong>
            <span>Lihat lima author beserta informasi singkatnya.</span>
            <p><a href="{{ route('authors.index') }}">Lihat author</a></p>
        </div>
    </div>

    <div class="section-heading">
        <h2>Katalog Buku</h2>
    </div>

    @if ($books->isEmpty())
        <p class="muted">Belum ada buku tersedia. Silakan kembali lagi nanti.</p>
    @else
        <form method="POST" action="{{ route('sales.store') }}">
            @csrf
            <div class="table-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Buku</th>
                                <th scope="col">Genre</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Stok</th>
                                <th scope="col">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($books as $index => $book)
                                <tr>
                                    <td>
                                        <span class="name">{{ $book->title }}</span><br>
                                        <span class="muted">{{ $book->authorRecord?->name ?? $book->author }}</span>
                                        @if ($book->description)
                                            <br>{{ $book->description }}
                                        @endif
                                        <input type="hidden" name="items[{{ $index }}][book_id]" value="{{ $book->id }}">
                                    </td>
                                    <td>{{ $book->genre }}</td>
                                    <td>Rp{{ number_format($book->price, 0, ',', '.') }}</td>
                                    <td>{{ $book->stock }}</td>
                                    <td>
                                        <label class="field" for="quantity-{{ $book->id }}">
                                            <span class="muted">Jumlah</span>
                                            <input id="quantity-{{ $book->id }}" type="number" name="items[{{ $index }}][quantity]" min="0" max="{{ min($book->stock, 1000) }}" value="{{ old("items.$index.quantity", 0) }}">
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section-heading">
                <h2>Data Pemesan</h2>
            </div>
            <div class="form-card">
                <div class="form-grid">
                    <label class="field">
                        Nama
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" maxlength="255" required autocomplete="name">
                    </label>
                    <label class="field">
                        Nomor telepon
                        <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" maxlength="30" required autocomplete="tel">
                    </label>
                    <label class="field field-wide">
                        Email <span class="muted">(opsional)</span>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" maxlength="255" autocomplete="email">
                    </label>
                    <label class="field field-wide">
                        Metode pembayaran
                        <select name="payment_method" required>
                            <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                            <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Transfer bank</option>
                        </select>
                    </label>
                </div>
                <button type="submit">Buat Pesanan</button>
                <p class="muted">Pesanan menunggu konfirmasi pembayaran oleh admin. Pembayaran online belum terhubung.</p>
            </div>
        </form>
    @endif
@endsection
