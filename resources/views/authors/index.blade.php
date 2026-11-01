<div>
    <!-- It always seems impossible until it is done. - Nelson Mandela -->
</div>
@extends('layouts.catalog')

@section('title', 'Daftar Penulis')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
        <div class="relative overflow-hidden rounded-[2rem] bg-emerald-950 px-6 py-10 text-white sm:px-10 sm:py-12">
            <div class="absolute -right-12 -top-24 size-72 rounded-full border border-white/10"></div>
            <div class="absolute -right-2 -top-14 size-52 rounded-full border border-white/10"></div>
            <div class="relative max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">Di balik setiap cerita</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Kenali para penulis.</h1>
                <p class="mt-4 max-w-xl text-base leading-7 text-emerald-100/85 sm:text-lg sm:leading-8">Temui sosok dan karya yang menghadirkan cerita-cerita pilihan di Ruang Baca.</p>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between gap-4">
            <p class="text-sm font-semibold text-stone-600">Suara di balik halaman</p>
            <span class="rounded-full bg-white px-4 py-2 text-xs font-bold text-emerald-900 shadow-sm">{{ count($authors) }} penulis</span>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($authors as $author)
                <article class="group rounded-3xl border border-stone-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl hover:shadow-stone-900/[0.06] sm:p-7">
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-xl font-black text-amber-950 transition group-hover:bg-emerald-950 group-hover:text-amber-200">{{ mb_substr($author['name'], 0, 1) }}</div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-800">{{ $author['nationality'] ?: 'Penulis' }}</p>
                            <h2 class="mt-1 text-lg font-black leading-snug text-stone-950">{{ $author['name'] }}</h2>
                        </div>
                    </div>
                    <p class="mt-3 min-h-12 text-sm leading-6 text-stone-600">{{ $author['description'] ?: 'Penulis dan kontributor Ruang Baca.' }}</p>
                    <div class="mt-5 flex items-center justify-between border-t border-stone-100 pt-4 text-sm text-stone-600">
                        <span>Karya di katalog</span>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 font-bold text-emerald-900">{{ $author['books_count'] }} buku</span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
