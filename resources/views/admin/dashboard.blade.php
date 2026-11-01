<div>
    <!-- I begin to speak only when I am certain what I will say is not better left unsaid. - Cato the Younger -->
</div>
@extends('layouts.catalog')

@section('title', 'Dashboard Admin')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Area administrator</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-stone-950 sm:text-5xl">Dashboard admin</h1>
                <p class="mt-4 text-lg text-stone-600">Login sebagai <strong class="text-stone-950">{{ auth()->user()->name }}</strong> · {{ auth()->user()->email }}</p>
            </div>
            <a href="{{ route('home') }}" class="w-fit rounded-full border border-stone-300 bg-white px-5 py-3 text-sm font-bold text-stone-800 hover:border-emerald-700 hover:text-emerald-900">Lihat katalog <span aria-hidden="true">→</span></a>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Total Buku', 'count' => $bookCount, 'icon' => 'B'],
                ['label' => 'Total Genre', 'count' => $genreCount, 'icon' => 'G'],
                ['label' => 'Total Penulis', 'count' => $authorCount, 'icon' => 'P'],
                ['label' => 'Total Pesanan', 'count' => $saleCount, 'icon' => 'O'],
            ] as $stat)
                <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-stone-600">{{ $stat['label'] }}</p>
                        <span class="flex size-10 items-center justify-center rounded-2xl bg-emerald-50 font-black text-emerald-900">{{ $stat['icon'] }}</span>
                    </div>
                    <p class="mt-5 text-4xl font-black tracking-tight text-stone-950">{{ $stat['count'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="mt-8 grid gap-5 lg:grid-cols-2">
            <article class="rounded-3xl bg-emerald-950 p-7 text-white sm:p-9">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">Katalog API</p>
                <h2 class="mt-3 text-2xl font-black">Data buku, genre, dan penulis</h2>
                <p class="mt-3 max-w-lg text-sm leading-6 text-emerald-100">Aplikasi menyediakan halaman katalog untuk pengunjung serta API JSON untuk pengujian dan integrasi.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ url('/api/books') }}" class="rounded-full bg-white px-4 py-2.5 text-sm font-bold text-emerald-950 hover:bg-emerald-50">API Buku</a>
                    <a href="{{ url('/api/genres') }}" class="rounded-full bg-white/10 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/20">API Genre</a>
                    <a href="{{ url('/api/authors') }}" class="rounded-full bg-white/10 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/20">API Penulis</a>
                </div>
            </article>
            <article class="rounded-3xl border border-stone-200 bg-white p-7 sm:p-9">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-800">Sesi aman</p>
                <h2 class="mt-3 text-2xl font-black text-stone-950">Anda masuk sebagai admin</h2>
                <p class="mt-3 text-sm leading-6 text-stone-600">Dashboard ini hanya tersedia untuk akun administrator. Logout menutup sesi dan memperbarui token keamanan.</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-bold text-stone-800 hover:border-red-300 hover:bg-red-50 hover:text-red-800">Keluar dari akun</button>
                </form>
            </article>
        </div>
    </section>
@endsection
