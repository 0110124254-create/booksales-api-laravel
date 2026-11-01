<div>
    <!-- Act only according to that maxim whereby you can, at the same time, will that it should become a universal law. - Immanuel Kant -->
</div>
@extends('layouts.catalog')

@section('title', 'Daftar Genre')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Jelajahi koleksi</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-stone-950 sm:text-5xl">Genre buku</h1>
            <p class="mt-4 text-lg leading-8 text-stone-600">Setiap genre punya dunia sendiri. Temukan kategori bacaan yang paling menarik untukmu.</p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($genres as $genre)
                <article class="rounded-3xl border border-stone-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-lg font-black text-emerald-900">{{ str_pad((string) $genre['id'], 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-900">Koleksi</span>
                    </div>
                    <h2 class="mt-6 text-xl font-black text-stone-950">{{ $genre['name'] }}</h2>
                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ $genre['description'] ?: 'Jelajahi pilihan buku dalam genre ini.' }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
