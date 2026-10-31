@extends('layouts.catalog')

@section('title', 'Kelola Buku')

@section('content')
    <p class="eyebrow">Area admin</p>
    <div class="section-heading">
        <h1>Kelola Buku</h1>
        <a class="button" href="{{ route('admin.books.create') }}">Tambah Buku</a>
    </div>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Author</th>
                        <th scope="col">Genre</th>
                        <th scope="col">Harga</th>
                        <th scope="col">Stok</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td class="name">{{ $book->title }}</td>
                            <td>{{ $book->authorRecord?->name ?? $book->author }}</td>
                            <td>{{ $book->genre }}</td>
                            <td>Rp{{ number_format($book->price, 0, ',', '.') }}</td>
                            <td>{{ $book->stock }}</td>
                            <td>{{ $book->is_active ? 'Aktif' : 'Tidak aktif' }}</td>
                            <td>
                                <div class="inline-actions">
                                    <a class="button button-secondary" href="{{ route('admin.books.edit', $book) }}">Ubah</a>
                                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Hapus buku ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada data buku.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $books->links() }}</div>
@endsection
