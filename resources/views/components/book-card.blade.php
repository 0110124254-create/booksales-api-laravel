@props(['book'])

<article class="book-card" data-book-card data-price="{{ $book->price }}" data-created="{{ $book->created_at?->timestamp ?? 0 }}">
    <a class="book-card__cover-link" href="{{ route('catalog.books.show', $book) }}" aria-label="Lihat detail {{ $book->title }}">
        <div class="book-card__cover-frame">
            @if ($book->cover)
                <img src="{{ asset($book->cover) }}" alt="{{ $book->title }}" width="400" height="600" loading="lazy" onerror="this.hidden=true; this.nextElementSibling.hidden=false">
                <div class="book-card__fallback" hidden>{{ $book->title }}</div>
            @else
                <div class="book-card__fallback">{{ $book->title }}</div>
            @endif
        </div>
        <h3 class="book-card__title">{{ $book->title }}</h3>
    </a>
    <p class="book-card__author">{{ $book->author }}</p>
    <p class="book-card__price">Rp {{ number_format($book->price, 0, ',', '.') }}</p>
    <form method="POST" action="{{ route('cart.store', $book) }}" class="book-card__action">
        @csrf
        <button type="submit">Tambah ke keranjang</button>
    </form>
</article>
