@extends('layouts.catalog')

@section('title', $selectedGenre ? 'Buku '.$selectedGenre->name : 'Beranda')

@section('content')
    @if (! $selectedGenre && ! $search && $featuredBooks->isNotEmpty())
        <section class="catalog-hero" aria-label="Buku pilihan">
            <img class="catalog-hero__photo" src="{{ asset('images/hero/library.jpg') }}" alt="Deretan buku di perpustakaan" width="2000" height="2670" onerror="this.hidden=true">
            <div class="catalog-hero__shade"></div>
            <div class="catalog-hero__content">
                <div class="catalog-hero__copy" data-feature-copy>
                    @php($featuredBook = $featuredBooks->first())
                    <p class="eyebrow">Ruang untuk cerita</p>
                    <h1 data-feature-title>{{ $featuredBook->title }}</h1>
                    <p class="catalog-hero__author" data-feature-author>{{ $featuredBook->author }}</p>
                    <p class="catalog-hero__description" data-feature-description>{{ $featuredBook->description ?: 'Temukan kisah dan gagasan baru di setiap halaman.' }}</p>
                    <a class="catalog-hero__link" href="{{ route('catalog.books.show', $featuredBook) }}" data-feature-link>Lihat buku <span aria-hidden="true">→</span></a>
                </div>
                <div class="featured-thumbnails" role="group" aria-label="Pilih buku unggulan">
                    @foreach ($featuredBooks as $index => $book)
                        <button type="button" class="featured-thumbnails__item {{ $index === 0 ? 'is-active' : '' }}" data-featured-book
                            data-title="{{ $book->title }}" data-author="{{ $book->author }}" data-description="{{ $book->description ?: 'Temukan kisah dan gagasan baru di setiap halaman.' }}" data-url="{{ route('catalog.books.show', $book) }}" aria-label="Tampilkan {{ $book->title }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                            @if ($book->cover)
                                <img src="{{ asset($book->cover) }}" alt="{{ $book->title }}" width="400" height="600" loading="lazy" onerror="this.hidden=true">
                            @else
                                <span class="featured-thumbnails__empty">{{ $book->title }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            <p class="catalog-hero__credit">Foto: <a href="https://unsplash.com/@ninjason" target="_blank" rel="noopener">Jason Leung</a> / <a href="https://unsplash.com/photos/a-library-aisle-with-bookshelves-and-sunlight-through-window-xmeRBPunJR8" target="_blank" rel="noopener">Unsplash License</a></p>
        </section>
    @elseif ($selectedGenre)
        <section class="catalog-page-heading">
            <a href="{{ route('catalog.genres.index') }}" class="text-link">← Semua genre</a>
            <p class="eyebrow">Koleksi pilihan</p>
            <h1>Buku genre {{ $selectedGenre->name }}</h1>
            <p>{{ $selectedGenre->description ?: 'Temukan bacaan pilihan dalam genre ini.' }}</p>
        </section>
    @endif

    <nav class="catalog-tabs" aria-label="Kategori koleksi" data-catalog-tabs>
        <button type="button" class="is-active" data-tab="Terbaru" aria-pressed="true">Terbaru</button>
        <button type="button" data-tab="Promo" aria-pressed="false">Promo</button>
        <button type="button" data-tab="Buku" aria-pressed="false">Buku</button>
        <button type="button" data-tab="E-Book" aria-pressed="false">E-Book</button>
        <button type="button" data-tab="Audio" aria-pressed="false">Audio</button>
        <button type="button" data-tab="Event" aria-pressed="false">Event</button>
    </nav>

    <section id="koleksi" class="catalog-section">
        <div class="catalog-section__heading">
            <div>
                <p class="eyebrow">Dari rak pilihan</p>
                <h2><span class="book-mark" aria-hidden="true">▤</span> {{ $selectedGenre ? 'Koleksi '.$selectedGenre->name : 'Terbaru' }}</h2>
                @if ($selectedGenre)
                    <a class="catalog-see-all" href="{{ route('books.catalog') }}#koleksi">Lihat semua buku →</a>
                @endif
            </div>
            @if ($search)
                <p class="catalog-section__summary">Hasil pencarian untuk “{{ $search }}”.</p>
            @else
                <p class="catalog-section__summary">{{ $books->count() }} buku pilihan · {{ $genreCount }} genre · {{ $authorCount }} penulis</p>
            @endif
        </div>

        @if ($selectedGenre || $search)
            <form method="GET" action="{{ route('books.catalog') }}" class="catalog-search-inline">
                @if ($selectedGenre)<input type="hidden" name="genre" value="{{ $selectedGenre->name }}">@endif
                <label class="sr-only" for="catalog-query">Cari buku, penulis, atau deskripsi</label>
                <input id="catalog-query" type="search" name="q" value="{{ $search }}" placeholder="Cari judul buku atau penulis...">
                <button type="submit">Cari buku</button>
            </form>
        @endif

        <div class="book-grid" data-book-grid>
            @forelse ($books as $book)
                <x-book-card :book="$book" />
            @empty
                <p class="catalog-empty">Belum ada buku yang cocok dengan pilihan ini. <a href="{{ route('books.catalog') }}">Lihat semua buku</a></p>
            @endforelse
        </div>
        <p class="catalog-empty" data-catalog-empty hidden>Belum ada koleksi untuk kategori ini.</p>
    </section>

    <script>
        document.querySelectorAll('[data-featured-book]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('[data-featured-book]').forEach((item) => {
                    item.classList.toggle('is-active', item === button);
                    item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
                });
                document.querySelector('[data-feature-title]').textContent = button.dataset.title;
                document.querySelector('[data-feature-author]').textContent = button.dataset.author;
                document.querySelector('[data-feature-description]').textContent = button.dataset.description;
                document.querySelector('[data-feature-link]').href = button.dataset.url;
            });
        });

        const catalogTabs = document.querySelector('[data-catalog-tabs]');
        const bookGrid = document.querySelector('[data-book-grid]');
        const catalogEmpty = document.querySelector('[data-catalog-empty]');
        catalogTabs?.querySelectorAll('[data-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                catalogTabs.querySelectorAll('[data-tab]').forEach((item) => {
                    item.classList.toggle('is-active', item === tab);
                    item.setAttribute('aria-pressed', item === tab ? 'true' : 'false');
                });
                const cards = [...(bookGrid?.querySelectorAll('[data-book-card]') ?? [])];
                const type = tab.dataset.tab;
                cards.forEach((card) => {
                    const show = type === 'Terbaru' || type === 'Buku';
                    card.hidden = !show;
                });
                if (catalogEmpty) catalogEmpty.hidden = cards.some((card) => !card.hidden);
            });
        });
    </script>
@endsection
