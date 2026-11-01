<div>
    <!-- It always seems impossible until it is done. - Nelson Mandela -->
</div>
@extends('layouts.catalog')

@section('title', 'Daftar Penulis')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Di balik setiap cerita</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-stone-950 sm:text-5xl">Kenali para penulis</h1>
            <p class="mt-4 text-lg leading-8 text-stone-600">Lihat lima penulis pilihan beserta karya dan cerita di balik buku mereka.</p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($authors as $author)
                <article class="rounded-3xl border border-stone-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
                    <div class="flex size-14 items-center justify-center rounded-full bg-amber-100 text-xl font-black text-amber-950">{{ mb_substr($author['name'], 0, 1) }}</div>
                    <p class="mt-6 text-xs font-bold uppercase tracking-[0.16em] text-emerald-800">{{ $author['nationality'] ?: 'Penulis' }}</p>
                    <h2 class="mt-2 text-xl font-black text-stone-950">{{ $author['name'] }}</h2>
                    <p class="mt-3 min-h-12 text-sm leading-6 text-stone-600">{{ $author['description'] ?: 'Penulis dan kontributor Ruang Baca.' }}</p>
                    <div class="mt-5 border-t border-stone-100 pt-4 text-sm text-stone-600">
                        <span class="font-bold text-stone-900">{{ $author['books_count'] }}</span> buku di katalog
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
