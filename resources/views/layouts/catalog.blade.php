<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') · Ruang Baca</title>
    <meta name="description" content="Jelajahi koleksi buku, genre, dan penulis di Ruang Baca.">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[#f8f7f3] font-sans text-stone-900 antialiased">
    <header class="sticky top-0 z-30 border-b border-stone-200/80 bg-white/95 shadow-sm shadow-stone-900/[0.03] backdrop-blur">
        <nav class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-5 py-4 sm:px-8" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-lg font-black tracking-tight text-stone-950 transition hover:opacity-80 sm:text-xl">
                <span class="flex size-11 items-center justify-center rounded-2xl bg-emerald-950 text-sm font-black tracking-wide text-amber-300 shadow-sm">RB</span>
                <span>ruang<span class="text-emerald-800">baca</span></span>
            </a>

            <div class="flex flex-wrap items-center gap-1 text-sm font-semibold text-stone-600 sm:gap-2">
                <a href="{{ route('home') }}" @class(['rounded-full px-3 py-2 transition hover:bg-stone-100 hover:text-stone-950 sm:px-4', 'bg-stone-100 text-stone-950' => request()->routeIs('home', 'books.catalog')]) @if (request()->routeIs('home', 'books.catalog')) aria-current="page" @endif>Beranda</a>
                <a href="{{ route('catalog.genres.index') }}" @class(['rounded-full px-3 py-2 transition hover:bg-stone-100 hover:text-stone-950 sm:px-4', 'bg-stone-100 text-stone-950' => request()->routeIs('catalog.genres.*')]) @if (request()->routeIs('catalog.genres.*')) aria-current="page" @endif>Genre</a>
                <a href="{{ route('catalog.authors.index') }}" @class(['rounded-full px-3 py-2 transition hover:bg-stone-100 hover:text-stone-950 sm:px-4', 'bg-stone-100 text-stone-950' => request()->routeIs('catalog.authors.*')]) @if (request()->routeIs('catalog.authors.*')) aria-current="page" @endif>Penulis</a>

                @auth
                    @can('access-admin')
                        <a href="{{ route('admin.dashboard') }}" @class(['rounded-full px-3 py-2 text-emerald-900 transition hover:bg-emerald-50 sm:px-4', 'bg-emerald-50 font-bold' => request()->routeIs('admin.dashboard')]) @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-stone-300 px-4 py-2 text-stone-700 transition hover:border-red-300 hover:bg-red-50 hover:text-red-800">Keluar</button>
                        </form>
                    @endcan
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-emerald-950 px-5 py-2.5 text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-700/20">Login Admin</a>
                @endauth
            </div>
        </nav>
    </header>

    @if (session('status'))
        <div class="mx-auto mt-6 max-w-7xl px-5 sm:px-8">
            <p role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-900">{{ session('status') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mx-auto mt-6 max-w-7xl px-5 sm:px-8">
            <p role="alert" class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-900">{{ session('error') }}</p>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-emerald-900/10 bg-emerald-950 text-emerald-50">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-8 text-sm text-emerald-100/75 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p class="font-semibold text-white">Ruang Baca <span class="px-1 text-amber-300">·</span> Temukan cerita berikutnya.</p>
            <p>Katalog buku, genre, dan penulis pilihan.</p>
        </div>
    </footer>
</body>
</html>
