<div>
    <!-- Simplicity is the ultimate sophistication. - Leonardo da Vinci -->
</div>
@extends('layouts.catalog')

@section('title', 'Katalog Buku')

@section('content')
    <section class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
        <div class="max-w-2xl">
            <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-emerald-900">
                <span class="size-2 rounded-full bg-emerald-600"></span>
                Cerita dimulai di sini
            </p>
            <h1 class="text-5xl font-black leading-[1.05] tracking-tight text-stone-950 sm:text-6xl">Temukan buku yang <span class="text-emerald-800">memanggil</span> namamu.</h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-stone-600">Jelajahi pilihan bacaan dari penulis Indonesia, temukan genre favoritmu, dan temukan ide baru di setiap halaman.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#koleksi" class="rounded-full bg-emerald-950 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-emerald-800">Jelajahi buku</a>
                <a href="{{ route('catalog.genres.index') }}" class="rounded-full border border-stone-300 bg-white px-6 py-3.5 text-sm font-bold text-stone-800 transition hover:border-stone-500">Lihat genre</a>
            </div>
            <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 border-t border-stone-200 pt-6 text-sm text-stone-600">
                <span><strong class="text-xl text-stone-950">{{ $books->count() }}</strong> buku pilihan</span>
                <span><strong class="text-xl text-stone-950">{{ $genreCount }}</strong> genre</span>
                <span><strong class="text-xl text-stone-950">{{ $authorCount }}</strong> penulis</span>
            </div>
        </div>

        <div class="relative mx-auto w-full max-w-xl">
            <div class="absolute -left-5 top-6 size-24 rounded-full bg-amber-200/70 blur-2xl sm:size-36"></div>
            <div class="relative grid grid-cols-2 gap-3 rounded-[2rem] border border-stone-200 bg-white p-4 shadow-xl shadow-stone-900/5 sm:gap-4 sm:p-6">
                <div class="flex min-h-48 flex-col justify-between rounded-3xl bg-emerald-950 p-5 text-white sm:min-h-60 sm:p-7">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-200">Bacaan pilihan</span>
                    <div>
                        <p class="text-2xl font-black leading-tight sm:text-3xl">Lembar demi lembar, dunia baru.</p>
                        <span class="mt-4 inline-block rounded-full bg-white/10 px-3 py-1 text-xs text-emerald-100">01 — Ruang Baca</span>
                    </div>
                </div>
                <div class="flex min-h-48 flex-col justify-between rounded-3xl bg-amber-100 p-5 sm:min-h-60 sm:p-7">
                    <span class="text-4xl" aria-hidden="true">✳</span>
                    <div>
                        <p class="text-2xl font-black leading-tight text-stone-950 sm:text-3xl">Ide baik berawal dari membaca.</p>
                        <p class="mt-3 text-sm text-stone-600">Satu buku bisa membuka banyak jalan.</p>
                    </div>
                </div>
                <div class="col-span-2 flex items-center justify-between gap-4 rounded-3xl bg-stone-100 px-5 py-4 sm:px-7">
                    <div>
                        <p class="text-sm font-bold text-stone-950">Cari bacaan favoritmu</p>
                        <p class="mt-1 text-sm text-stone-600">Jelajahi pilihan dari penulis lokal.</p>
                    </div>
                    <a href="{{ route('catalog.authors.index') }}" class="shrink-0 rounded-full bg-white px-4 py-2 text-sm font-bold text-emerald-950 shadow-sm hover:bg-emerald-50">Kenali penulis <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section id="koleksi" class="bg-white">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-16">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Dari rak pilihan</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-stone-950 sm:text-4xl">Koleksi buku</h2>
                </div>
                <p class="max-w-md text-sm leading-6 text-stone-600">Buku di halaman ini dimuat langsung dari katalog. Genre dan penulis juga bisa kamu jelajahi.</p>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($books as $book)
                    <article class="group overflow-hidden rounded-3xl border border-stone-200 bg-stone-50 transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-stone-900/5">
                        <div class="flex min-h-48 flex-col justify-between bg-emerald-950 p-6 text-white">
                            <span class="w-fit rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100">{{ $book->genre }}</span>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-200">Ruang Baca · {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3 class="mt-3 text-2xl font-black leading-tight">{{ $book->title }}</h3>
                            </div>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <p class="text-sm font-semibold text-stone-700">Oleh {{ $book->author }}</p>
                                @if ($book->description)
                                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ $book->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center justify-between border-t border-stone-200 pt-4">
                                <p class="text-lg font-black text-emerald-950">Rp{{ number_format($book->price, 0, ',', '.') }}</p>
                                <span @class(['rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-100 text-emerald-900' => $book->stock > 0, 'bg-stone-200 text-stone-600' => $book->stock === 0])>
                                    {{ $book->stock > 0 ? 'Stok '.$book->stock : 'Stok habis' }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-stone-300 bg-stone-50 px-6 py-12 text-center">
                        <p class="text-lg font-bold text-stone-900">Katalog buku sedang disiapkan.</p>
                        <p class="mt-2 text-sm text-stone-600">Jalankan seeder aplikasi untuk menampilkan lima buku contoh.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
