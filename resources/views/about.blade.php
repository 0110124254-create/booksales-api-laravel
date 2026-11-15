@extends('layouts.catalog')

@section('title', 'Tentang Kami')

@section('content')
<section class="mx-auto max-w-5xl px-5 py-16 sm:px-8 sm:py-24">
    <div class="rounded-[2rem] bg-violet-950 p-8 text-white sm:p-14">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">Tentang Ruang Baca</p>
        <h1 class="mt-4 text-4xl font-black sm:text-5xl">Buku untuk membuka banyak dunia.</h1>
        <p class="mt-6 max-w-3xl text-lg leading-8 text-violet-100">Ruang Baca adalah toko buku yang membantu pembaca menemukan bacaan baru dari penulis dan genre pilihan. Kami ingin membuat pengalaman memilih dan memesan buku terasa mudah, nyaman, dan aman.</p>
        <a href="{{ route('books.catalog') }}" class="mt-8 inline-flex rounded-full bg-white px-6 py-3 font-bold text-violet-950 hover:bg-violet-50">Jelajahi katalog</a>
    </div>
    <div class="mt-8 grid gap-5 sm:grid-cols-3">
        @foreach ([['Pilihan bacaan', 'Katalog berisi berbagai genre dan penulis.'], ['Belanja praktis', 'Pesan buku melalui keranjang dan checkout online.'], ['Bantuan pelanggan', 'Kirim pertanyaan langsung melalui halaman kontak.']] as [$title, $description])
            <article class="rounded-3xl border border-stone-200 bg-white p-6"><h2 class="font-black text-stone-950">{{ $title }}</h2><p class="mt-2 text-sm leading-6 text-stone-600">{{ $description }}</p></article>
        @endforeach
    </div>
</section>
@endsection
