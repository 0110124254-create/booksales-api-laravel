@extends('layouts.catalog')

@section('title', 'Katalog Buku')

@section('content')
    <p class="eyebrow">Book Sales</p>
    <h1>Katalog Buku</h1>
    <p class="intro">Pilih data yang ingin dilihat. Halaman ini menampilkan daftar genre dan author dari contoh data pada Model.</p>

    <div class="catalog-grid">
        <a class="catalog-link" href="{{ route('genres.index') }}">
            <strong>Daftar Genre</strong>
            <span>Lihat lima kategori buku beserta deskripsinya.</span>
        </a>
        <a class="catalog-link" href="{{ route('authors.index') }}">
            <strong>Daftar Author</strong>
            <span>Lihat lima author beserta informasi singkatnya.</span>
        </a>
    </div>
@endsection
