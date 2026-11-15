@extends('layouts.catalog')

@section('title', $book->title)

@section('content')
    <section class="book-detail">
    <a href="{{ route('books.catalog') }}" class="text-link">← Kembali ke katalog</a>
        <div class="book-detail__layout">
            <div class="book-detail__cover">
                @if ($book->cover)
                    <img src="{{ asset($book->cover) }}" alt="{{ $book->title }}" width="400" height="600" onerror="this.hidden=true; this.nextElementSibling.hidden=false">
                    <div class="book-card__fallback" hidden>{{ $book->title }}</div>
                @else
                    <div class="book-card__fallback">{{ $book->title }}</div>
                @endif
            </div>
            <div>
                <p class="eyebrow">{{ $book->genre }}</p>
                <h1>{{ $book->title }}</h1>
                <p class="book-detail__author">{{ $book->author }}</p>
                <p class="book-detail__description">{{ $book->description }}</p>
                <p class="book-detail__price">Rp {{ number_format($book->price, 0, ',', '.') }}</p>
                <form method="POST" action="{{ route('cart.store', $book) }}">@csrf<button type="submit" class="primary-button">Tambah ke keranjang</button></form>
            </div>
        </div>
    </section>
@endsection
