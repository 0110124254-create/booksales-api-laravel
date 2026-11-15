<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'Beranda') · ruangbaca</title>
    <meta name="description" content="Jelajahi koleksi buku, genre, dan penulis di Ruang Baca.">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="catalog-body">
    @php
        $catalogGenres = \App\Models\Genre::query()->orderBy('name')->pluck('name');
        $cartCount = collect(session('cart', []))->sum();
    @endphp
    <header class="catalog-header">
        <div class="catalog-topbar">
            <a href="{{ route('home') }}" class="catalog-logo" aria-label="ruangbaca beranda">ruang<span>baca</span></a>
            <form method="GET" action="{{ route('books.catalog') }}" class="header-search" role="search">
                <label class="sr-only" for="header-category">Kategori buku</label>
                <select id="header-category" name="genre">
                    <option value="">Semua kategori</option>
                    @foreach ($catalogGenres as $genre)
                        <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                    @endforeach
                </select>
                <span class="header-search__divider" aria-hidden="true"></span>
                <label class="sr-only" for="header-query">Cari buku atau penulis</label>
                <input id="header-query" type="search" name="q" value="{{ request('q') }}" placeholder="Cari buku, penulis...">
                <button type="submit" aria-label="Cari">⌕</button>
            </form>
            <a href="{{ route('cart.index') }}" class="cart-link" aria-label="Keranjang, {{ $cartCount }} item">
                <span class="cart-link__icon" aria-hidden="true">🛒</span>
                <span class="cart-link__label">Keranjang</span>
                @if ($cartCount > 0)<span class="cart-link__count">{{ $cartCount }}</span>@endif
            </a>
        </div>

        <nav class="secondary-nav secondary-nav--desktop" aria-label="Navigasi utama">
            @include('layouts.partials.catalog-links')
        </nav>
        <details class="mobile-menu">
            <summary><span aria-hidden="true">☰</span> Menu</summary>
            <nav class="secondary-nav secondary-nav--mobile" aria-label="Navigasi utama">
                @include('layouts.partials.catalog-links')
            </nav>
        </details>
    </header>

    @if (session('status'))
        <div class="catalog-notice" role="status">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="catalog-notice catalog-notice--error" role="alert">{{ session('error') }}</div>
    @endif

    <main>@yield('content')</main>

    <footer class="catalog-footer">
        <p><strong>ruangbaca</strong> · Temukan cerita berikutnya.</p>
        <p>Katalog buku, genre, dan penulis pilihan.</p>
        <p class="catalog-footer__credit">Foto hero oleh <a href="https://unsplash.com/@ninjason" target="_blank" rel="noopener">Jason Leung</a>, digunakan berdasarkan <a href="https://unsplash.com/license" target="_blank" rel="noopener">Unsplash License</a>.</p>
    </footer>
</body>
</html>
