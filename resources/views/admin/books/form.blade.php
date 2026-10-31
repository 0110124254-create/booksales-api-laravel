@extends('layouts.catalog')

@section('title', $book->exists ? 'Ubah Buku' : 'Tambah Buku')

@section('content')
    <p class="eyebrow">Area admin</p>
    <h1>{{ $book->exists ? 'Ubah Buku' : 'Tambah Buku' }}</h1>
    <p class="intro">Isi data buku yang akan ditampilkan pada katalog.</p>

    <form class="form-card" method="POST" action="{{ $book->exists ? route('admin.books.update', $book) : route('admin.books.store') }}">
        @csrf
        @if ($book->exists)
            @method('PUT')
        @endif
        <div class="form-grid">
            <label class="field field-wide">
                Judul buku
                <input type="text" name="title" value="{{ old('title', $book->title) }}" maxlength="255" required>
            </label>
            <label class="field">
                Author
                <select name="author_id" required>
                    <option value="">Pilih author</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" @selected((string) old('author_id', $book->author_id) === (string) $author->id)>{{ $author->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                Genre
                <input type="text" name="genre" value="{{ old('genre', $book->genre) }}" maxlength="100" required>
            </label>
            <label class="field">
                Harga (Rp)
                <input type="number" name="price" value="{{ old('price', $book->price) }}" min="1000" step="1" required>
            </label>
            <label class="field">
                Stok
                <input type="number" name="stock" value="{{ old('stock', $book->stock ?? 0) }}" min="0" max="1000000" step="1" required>
            </label>
            <label class="field field-wide">
                Deskripsi
                <textarea name="description" maxlength="5000">{{ old('description', $book->description) }}</textarea>
            </label>
        </div>
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox-field">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $book->is_active ?? true))>
            Tampilkan di katalog
        </label>
        <div class="form-actions">
            <button type="submit">Simpan</button>
            <a class="button button-secondary" href="{{ route('admin.books.index') }}">Kembali</a>
        </div>
    </form>
@endsection
