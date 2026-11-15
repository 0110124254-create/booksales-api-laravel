@extends('layouts.catalog')

@section('title', 'Pesanan Saya')

@section('content')
<section class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-800">Akun Anda</p><h1 class="mt-3 text-4xl font-black">Pesanan saya</h1>
    <div class="mt-8 space-y-4">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order->reference) }}" class="block rounded-2xl border border-stone-200 bg-white p-5 transition hover:border-violet-300 hover:shadow-md">
                <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-wider text-stone-500">{{ $order->reference }}</p><h2 class="mt-1 font-black">{{ $order->items->count() }} jenis buku · {{ $order->created_at->format('d/m/Y H:i') }}</h2></div><div class="text-right"><p class="font-black">Rp{{ number_format($order->total, 0, ',', '.') }}</p><p class="mt-1 text-sm text-violet-900">Pembayaran: {{ ucfirst($order->payment_status) }}</p></div></div>
            </a>
        @empty
            <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-10 text-center"><p class="font-bold">Belum ada pesanan.</p><a href="{{ route('books.catalog') }}" class="mt-4 inline-flex text-sm font-bold text-violet-900">Mulai belanja</a></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</section>
@endsection
