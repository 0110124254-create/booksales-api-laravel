<div>
    <!-- Act only according to that maxim whereby you can, at the same time, will that it should become a universal law. - Immanuel Kant -->
</div>
@extends('layouts.catalog')

@section('title', 'Daftar Genre')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
        <div class="relative overflow-hidden rounded-[2rem] bg-emerald-950 px-6 py-10 text-white sm:px-10 sm:py-12">
            <div class="absolute -right-12 -top-24 size-72 rounded-full border border-white/10"></div>
            <div class="absolute -right-2 -top-14 size-52 rounded-full border border-white/10"></div>
            <div class="relative max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">Jelajahi koleksi</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Temukan genre favoritmu.</h1>
                <p class="mt-4 max-w-xl text-base leading-7 text-emerald-100/85 sm:text-lg sm:leading-8">Setiap genre membuka dunia yang berbeda. Pilih suasana bacaan yang paling menarik untukmu.</p>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between gap-4">
            <p class="text-sm font-semibold text-stone-600">Jelajahi semua kategori bacaan</p>
            <span class="rounded-full bg-white px-4 py-2 text-xs font-bold text-emerald-900 shadow-sm">{{ count($genres) }} genre</span>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($genres as $genre)
                <article class="group rounded-3xl border border-stone-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl hover:shadow-stone-900/[0.06] sm:p-7">
                    <div class="flex items-center justify-between">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-black tracking-wide text-emerald-900 transition group-hover:bg-emerald-950 group-hover:text-white">{{ str_pad((string) $genre['id'], 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-900">Genre</span>
                    </div>
                    <h2 class="mt-6 text-xl font-black text-stone-950">{{ $genre['name'] }}</h2>
                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ $genre['description'] ?: 'Jelajahi pilihan buku dalam genre ini.' }}</p>
                    <div class="mt-6 border-t border-stone-100 pt-4 text-xs font-semibold uppercase tracking-[0.14em] text-emerald-800">Ruang untuk cerita baru</div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
