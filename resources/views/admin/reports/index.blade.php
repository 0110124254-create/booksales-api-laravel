@extends('layouts.catalog')

@section('title', 'Laporan Penjualan')

@section('content')
    <p class="eyebrow">Area admin</p>
    <h1>Laporan Penjualan</h1>
    <p class="intro">Ringkasan pendapatan menghitung pesanan yang sudah dikonfirmasi lunas.</p>

    <form class="form-card" method="GET" action="{{ route('admin.reports.index') }}">
        <div class="form-grid">
            <label class="field">
                Dari tanggal
                <input type="date" name="start_date" value="{{ $startDate }}" required>
            </label>
            <label class="field">
                Sampai tanggal
                <input type="date" name="end_date" value="{{ $endDate }}" required>
            </label>
        </div>
        <button type="submit">Tampilkan</button>
    </form>

    <div class="summary-grid">
        <div class="summary-card">
            <span>Pesanan lunas</span>
            <strong>{{ number_format($paidCount, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-card">
            <span>Total pendapatan</span>
            <strong>Rp{{ number_format($paidRevenue, 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="section-heading">
        <h2>Semua pesanan pada rentang tanggal</h2>
    </div>
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Referensi</th>
                        <th scope="col">Pemesan</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->created_at->format('d-m-Y H:i') }}</td>
                            <td><a href="{{ route('sales.show', $sale->reference) }}">{{ $sale->reference }}</a></td>
                            <td>{{ $sale->customer_name }}</td>
                            <td>Rp{{ number_format($sale->total, 0, ',', '.') }}</td>
                            <td><span class="status status-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Tidak ada pesanan pada rentang tanggal ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $sales->links() }}</div>
@endsection
