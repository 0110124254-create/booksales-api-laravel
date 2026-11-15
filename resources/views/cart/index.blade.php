@extends('layouts.catalog')

@section('title', 'Keranjang Belanja')

@section('content')
<section class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-800">Belanja Anda</p><h1 class="mt-3 text-4xl font-black">Keranjang</h1>
    @if ($items->isEmpty())
        <div class="mt-8 rounded-3xl border border-dashed border-stone-300 bg-white p-10 text-center"><p class="text-lg font-bold">Keranjang masih kosong.</p><a href="{{ route('books.catalog') }}" class="mt-5 inline-flex rounded-full bg-violet-950 px-5 py-3 font-bold text-white">Cari buku</a></div>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($items as $item)
                <article class="flex flex-col gap-4 rounded-2xl border border-stone-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div><h2 class="font-black">{{ $item['book']->title }}</h2><p class="mt-1 text-sm text-stone-600">Rp{{ number_format($item['book']->price, 0, ',', '.') }} per buku · stok {{ $item['book']->stock }}</p></div>
                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('cart.update', $item['book']) }}" class="flex items-center gap-2">@csrf @method('PATCH')<input aria-label="Jumlah {{ $item['book']->title }}" name="quantity" type="number" min="1" max="{{ min(100, $item['book']->stock) }}" value="{{ $item['quantity'] }}" class="w-20 rounded-lg border border-stone-300 px-3 py-2"><button class="text-sm font-bold text-violet-900">Ubah</button></form>
                        <strong class="min-w-28 text-right">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                        <form method="POST" action="{{ route('cart.destroy', $item['book']) }}">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700">Hapus</button></form>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6 flex flex-col items-end gap-4 rounded-2xl bg-white p-6"><p class="text-xl font-black">Total: Rp{{ number_format($total, 0, ',', '.') }}</p><a href="{{ route('checkout.create') }}" class="rounded-full bg-violet-950 px-6 py-3 font-bold text-white hover:bg-violet-800">Lanjut ke checkout</a></div>
    @endif
</section>
@endsection
