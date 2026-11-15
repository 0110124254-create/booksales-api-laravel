@extends('layouts.catalog')

@section('title', 'Checkout')

@section('content')
<section class="mx-auto grid max-w-6xl gap-8 px-5 py-12 sm:px-8 lg:grid-cols-[1fr_0.8fr]">
    <div class="rounded-3xl border border-stone-200 bg-white p-7 sm:p-9">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-800">Pembayaran aman</p><h1 class="mt-3 text-3xl font-black">Alamat pengiriman</h1>
        @if ($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('checkout.store') }}" class="mt-7 space-y-5">
            @csrf
            <div><label for="customer_phone" class="text-sm font-bold">Nomor telepon</label><input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone') }}" required maxlength="30" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3"></div>
            <div><label for="shipping_address" class="text-sm font-bold">Alamat lengkap</label><textarea id="shipping_address" name="shipping_address" rows="5" required maxlength="2000" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3">{{ old('shipping_address') }}</textarea></div>
            <div class="rounded-xl bg-violet-50 p-4 text-sm leading-6 text-violet-950">Pembayaran dilakukan melalui Midtrans sebelum pesanan diproses dan dikirim.</div>
            <button class="w-full rounded-full bg-violet-950 px-6 py-3.5 font-bold text-white hover:bg-violet-800">Lanjutkan pembayaran</button>
        </form>
    </div>
    <aside class="h-fit rounded-3xl bg-violet-950 p-7 text-white sm:p-9"><h2 class="text-xl font-black">Ringkasan belanja</h2><div class="mt-5 space-y-4">@foreach ($items as $item)<div class="flex justify-between gap-4 border-b border-white/15 pb-3 text-sm"><span>{{ $item['book']->title }} × {{ $item['quantity'] }}</span><strong>Rp{{ number_format($item['book']->price * $item['quantity'], 0, ',', '.') }}</strong></div>@endforeach</div><p class="mt-6 flex justify-between text-lg font-black"><span>Total</span><span>Rp{{ number_format($total, 0, ',', '.') }}</span></p></aside>
</section>
@endsection
