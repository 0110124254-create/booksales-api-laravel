<div>
    <!-- Simplicity is the ultimate sophistication. - Leonardo da Vinci -->
</div>
@extends('layouts.catalog')

@section('title', 'Katalog Buku')

@section('content')
    @php
        $bookCovers = [
            'Belajar Sains Dasar' => 'belajar-sains-dasar.svg',
            'Catatan Kehidupan' => 'catatan-kehidupan.svg',
            'Mengenal Teknologi' => 'mengenal-teknologi.svg',
            'Petualangan di Pulau' => 'petualangan-di-pulau.svg',
            'Sejarah Nusantara' => 'sejarah-nusantara.svg',
        ];
    @endphp

    <section class="mx-auto grid max-w-7xl gap-10 px-5 py-12 sm:px-8 sm:py-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-14 lg:py-20">
        <div class="max-w-2xl">
            <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-900">
                <span class="size-2 rounded-full bg-emerald-600"></span>
                Ruang untuk cerita
            </p>
            <h1 class="text-4xl font-black leading-[1.08] tracking-tight text-stone-950 sm:text-5xl lg:text-6xl">Temukan buku yang <span class="text-emerald-800">memanggil</span> namamu.</h1>
            <p class="mt-6 max-w-xl text-base leading-7 text-stone-600 sm:text-lg sm:leading-8">Jelajahi pilihan bacaan dari penulis Indonesia, temukan genre favoritmu, dan temukan ide baru di setiap halaman.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#koleksi" class="rounded-full bg-emerald-950 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:-translate-y-0.5 hover:bg-emerald-800">Jelajahi koleksi <span class="ml-1" aria-hidden="true">↓</span></a>
                <a href="{{ route('catalog.genres.index') }}" class="rounded-full border border-stone-300 bg-white px-6 py-3.5 text-sm font-bold text-stone-800 transition hover:border-emerald-700 hover:text-emerald-900">Lihat genre</a>
            </div>
            <div class="mt-10 flex flex-wrap gap-x-8 gap-y-4 border-t border-stone-200 pt-6 text-sm text-stone-600">
                <span><strong class="mr-1 text-2xl font-black tracking-tight text-stone-950">{{ $books->count() }}</strong> buku pilihan</span>
                <span><strong class="mr-1 text-2xl font-black tracking-tight text-stone-950">{{ $genreCount }}</strong> genre</span>
                <span><strong class="mr-1 text-2xl font-black tracking-tight text-stone-950">{{ $authorCount }}</strong> penulis</span>
            </div>
        </div>

        <div class="relative mx-auto w-full max-w-xl">
            <div class="absolute -inset-4 rounded-[2.5rem] bg-amber-200/50 blur-2xl"></div>
            <div class="relative min-h-[24rem] overflow-hidden rounded-[2rem] bg-emerald-950 px-6 py-7 shadow-2xl shadow-emerald-950/15 sm:min-h-[30rem] sm:px-8">
                <div class="absolute -right-16 -top-20 size-64 rounded-full border border-white/10"></div>
                <div class="absolute -right-5 -top-9 size-44 rounded-full border border-white/10"></div>
                <div class="absolute bottom-0 left-0 h-36 w-full bg-gradient-to-t from-black/25 to-transparent"></div>
                <div class="relative z-20 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-emerald-200">Rak pilihan</p>
                        <p class="mt-2 max-w-[13rem] text-xl font-bold leading-snug text-white sm:text-2xl">Satu buku, dunia yang berbeda.</p>
                    </div>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold text-emerald-50">Pilihan minggu ini</span>
                </div>

                @forelse ($books->take(3)->values() as $index => $featuredBook)
                    @php
                        $coverPosition = match ($index) {
                            0 => 'left-[12%] top-[34%] rotate-[-12deg]',
                            1 => 'left-1/2 top-[24%] z-10 -translate-x-1/2 rotate-[2deg]',
                            default => 'right-[12%] top-[34%] rotate-[12deg]',
                        };
                    @endphp
                    <div @class(['absolute w-32 overflow-hidden rounded-lg border-4 border-white/90 bg-white shadow-2xl shadow-black/30 transition duration-300 hover:z-20 hover:-translate-y-2 sm:w-40', $coverPosition])>
                        <img
                            src="{{ asset('images/books/'.($bookCovers[$featuredBook->title] ?? 'book-default.svg')) }}"
                            alt="Sampul buku {{ $featuredBook->title }}"
                            class="aspect-[3/4] w-full object-cover"
                            width="400"
                            height="600"
                            @if ($index > 0) loading="lazy" @endif
                        >
                    </div>
                @empty
                    <div class="absolute inset-x-8 bottom-20 rounded-2xl border border-white/15 bg-white/10 p-5 text-sm leading-6 text-emerald-50">
                        Katalog pilihan akan tampil di sini setelah buku ditambahkan.
                    </div>
                @endforelse

                <div class="absolute inset-x-6 bottom-6 z-20 flex items-center justify-between gap-4 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur sm:inset-x-8 sm:bottom-8 sm:px-5">
                    <div>
                        <p class="text-sm font-bold text-white">Baca, temukan, bertumbuh.</p>
                        <p class="mt-0.5 text-xs text-emerald-100/80">Cerita baik selalu punya tempat.</p>
                    </div>
                    <a href="{{ route('catalog.authors.index') }}" class="shrink-0 text-sm font-bold text-amber-200 transition hover:text-white">Para penulis <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section id="koleksi" class="scroll-mt-24 border-y border-stone-200/70 bg-white">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-16">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Dari rak pilihan</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-stone-950 sm:text-4xl">Koleksi buku</h2>
                </div>
                <p class="max-w-md text-sm leading-6 text-stone-600">Buku di halaman ini dimuat langsung dari katalog. Genre dan penulis juga bisa kamu jelajahi.</p>
            </div>

            <div class="mt-9 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($books as $book)
                    <article class="group overflow-hidden rounded-3xl border border-stone-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl hover:shadow-stone-900/[0.07]">
                        <div class="flex aspect-[4/3] items-center justify-center overflow-hidden bg-[#f1efe8] p-5 sm:p-7">
                            <img
                                src="{{ asset('images/books/'.($bookCovers[$book->title] ?? 'book-default.svg')) }}"
                                alt="Sampul buku {{ $book->title }}"
                                class="h-full w-auto rounded-sm object-contain shadow-xl shadow-stone-900/15 transition duration-300 group-hover:scale-[1.03]"
                                width="400"
                                height="600"
                                loading="lazy"
                            >
                        </div>
                        <div class="space-y-4 p-5 sm:p-6">
                            <div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-900">{{ $book->genre }}</span>
                                <h3 class="mt-4 text-xl font-black text-stone-950">{{ $book->title }}</h3>
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
