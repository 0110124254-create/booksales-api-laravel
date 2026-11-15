@extends('layouts.catalog')

@section('title', 'Detail Pesanan')

@section('content')
<section class="mx-auto max-w-4xl px-5 py-12 sm:px-8 sm:py-16">
    <a href="{{ route('orders.index') }}" class="text-sm font-bold text-violet-900">&larr; Kembali ke pesanan</a>
    <div class="mt-5 rounded-3xl border border-stone-200 bg-white p-7 sm:p-10">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-500">{{ $order->reference }}</p><h1 class="mt-2 text-3xl font-black">Detail pesanan</h1>
        <div class="mt-6 grid gap-4 rounded-2xl bg-stone-50 p-5 sm:grid-cols-2"><p><span class="text-sm text-stone-500">Status pembayaran</span><br><strong>{{ ucfirst($order->payment_status) }}</strong></p><p><span class="text-sm text-stone-500">Status pesanan</span><br><strong>{{ ucfirst($order->fulfillment_status) }}</strong></p><p class="sm:col-span-2"><span class="text-sm text-stone-500">Alamat pengiriman</span><br><strong>{{ $order->shipping_address }}</strong></p></div>
        <div class="mt-6 divide-y divide-stone-100">@foreach ($order->items as $item)<div class="flex justify-between gap-4 py-4"><span>{{ $item->book_title }} × {{ $item->quantity }}</span><strong>Rp{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</strong></div>@endforeach</div>
        <p class="flex justify-between border-t border-stone-200 pt-5 text-xl font-black"><span>Total</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></p>
        @if ($order->payment_status === 'pending' && $order->midtrans_redirect_url)<a href="{{ $order->midtrans_redirect_url }}" class="mt-6 inline-flex rounded-full bg-violet-950 px-6 py-3 font-bold text-white hover:bg-violet-800">Lanjutkan pembayaran</a>@endif
        @if ($order->payment_status === 'pending')<p class="mt-4 text-sm text-stone-500">Status akan diperbarui otomatis setelah Midtrans mengirim konfirmasi.</p>@endif
    </div>
</section>
@endsection
