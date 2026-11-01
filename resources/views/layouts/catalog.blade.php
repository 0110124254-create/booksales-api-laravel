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
<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <nav class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-xl font-black tracking-tight text-stone-950">
                <span class="flex size-11 items-center justify-center rounded-2xl bg-emerald-950 text-lg text-amber-300">RB</span>
                <span>ruang<span class="text-emerald-800">baca</span></span>
            </a>

            <div class="flex flex-wrap items-center gap-1 text-sm font-semibold text-stone-600 sm:gap-3">
                <a href="{{ route('home') }}" @class(['rounded-full px-3 py-2 hover:bg-stone-100 hover:text-stone-950', 'bg-stone-100 text-stone-950' => request()->routeIs('home', 'books.catalog')])>Beranda</a>
                <a href="{{ route('catalog.genres.index') }}" @class(['rounded-full px-3 py-2 hover:bg-stone-100 hover:text-stone-950', 'bg-stone-100 text-stone-950' => request()->routeIs('catalog.genres.*')])>Genre</a>
                <a href="{{ route('catalog.authors.index') }}" @class(['rounded-full px-3 py-2 hover:bg-stone-100 hover:text-stone-950', 'bg-stone-100 text-stone-950' => request()->routeIs('catalog.authors.*')])>Penulis</a>

                @auth
                    @can('access-admin')
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full px-3 py-2 text-emerald-900 hover:bg-emerald-50">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-stone-300 px-4 py-2 text-stone-700 hover:border-stone-500">Keluar</button>
                        </form>
                    @endcan
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-emerald-950 px-5 py-2.5 text-white hover:bg-emerald-800">Login Admin</a>
                @endauth
            </div>
        </nav>
    </header>

    @if (session('status'))
        <div class="mx-auto mt-6 max-w-7xl px-5 sm:px-8">
            <p role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-900">{{ session('status') }}</p>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-7 text-sm text-stone-500 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>Ruang Baca · Temukan cerita berikutnya.</p>
            <p>Katalog buku, genre, dan penulis.</p>
        </div>
    </footer>
</body>
</html>
