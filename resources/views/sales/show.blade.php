@extends('layouts.catalog')

@section('title', 'Detail Pesanan')

@section('content')
    <p class="eyebrow">Pesanan berhasil dibuat</p>
    <h1>Terima kasih, {{ $sale->customer_name }}</h1>
    <p class="intro">Simpan nomor referensi ini untuk memeriksa status pesanan.</p>

    <div class="summary-grid">
        <div class="summary-card">
            <span>Nomor pesanan</span>
            <strong>{{ $sale->reference }}</strong>
        </div>
        <div class="summary-card">
            <span>Status pembayaran</span>
            <strong><span class="status status-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span></strong>
        </div>
        <div class="summary-card">
            <span>Metode pembayaran</span>
            <strong>{{ $sale->payment_method === 'cash' ? 'Tunai' : 'Transfer bank' }}</strong>
        </div>
        <div class="summary-card">
            <span>Total</span>
            <strong>Rp{{ number_format($sale->total, 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Buku</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Harga satuan</th>
                        <th scope="col">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->book_title }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="intro">Admin akan mengonfirmasi pembayaran secara manual. Untuk transfer bank, hubungi admin untuk mendapatkan instruksi pembayaran.</p>
    <a class="button" href="{{ route('home') }}">Kembali ke katalog</a>
@endsection
