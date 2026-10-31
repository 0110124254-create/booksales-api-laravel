@extends('layouts.catalog')

@section('title', 'Pesanan')

@section('content')
    <p class="eyebrow">Area admin</p>
    <h1>Pesanan</h1>
    <p class="intro">Periksa pesanan dan konfirmasi pembayaran tunai atau transfer secara manual.</p>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Referensi</th>
                        <th scope="col">Pemesan</th>
                        <th scope="col">Buku</th>
                        <th scope="col">Total</th>
                        <th scope="col">Pembayaran</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale->reference) }}">{{ $sale->reference }}</a></td>
                            <td>{{ $sale->customer_name }}<br><span class="muted">{{ $sale->customer_phone }}</span></td>
                            <td>
                                @foreach ($sale->items as $item)
                                    {{ $item->book_title }} ({{ $item->quantity }})@if (! $loop->last), @endif
                                @endforeach
                            </td>
                            <td>Rp{{ number_format($sale->total, 0, ',', '.') }}</td>
                            <td>{{ $sale->payment_method === 'cash' ? 'Tunai' : 'Transfer bank' }}</td>
                            <td><span class="status status-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span></td>
                            <td>
                                @if ($sale->payment_status === 'pending')
                                    <div class="inline-actions">
                                        <form method="POST" action="{{ route('admin.sales.paid', $sale) }}">
                                            @csrf
                                            <button type="submit">Tandai Lunas</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.sales.cancel', $sale) }}" onsubmit="return confirm('Batalkan pesanan dan kembalikan stok?')">
                                            @csrf
                                            <button class="button-danger" type="submit">Batalkan</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="muted">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $sales->links() }}</div>
@endsection
