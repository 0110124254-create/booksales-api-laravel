@extends('layouts.catalog')

@section('title', 'Daftar Author')

@section('content')
    <p class="eyebrow">Katalog buku</p>
    <h1>Daftar Author</h1>
    <p class="intro">Lihat data author yang karyanya menjadi bagian dari katalog buku.</p>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama Author</th>
                        <th scope="col">Kebangsaan</th>
                        <th scope="col">Keterangan</th>
                        <th scope="col">Jumlah Buku</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($authors as $author)
                        <tr>
                            <td class="number">{{ $author->id }}</td>
                            <td class="name">{{ $author->name }}</td>
                            <td>{{ $author->nationality }}</td>
                            <td>{{ $author->description }}</td>
                            <td>{{ $author->books_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada data author.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
