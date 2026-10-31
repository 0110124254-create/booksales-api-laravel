@extends('layouts.catalog')

@section('title', 'Daftar Genre')

@section('content')
    <p class="eyebrow">Katalog buku</p>
    <h1>Daftar Genre</h1>
    <p class="intro">Lihat kategori buku yang tersedia pada sistem penjualan buku.</p>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama Genre</th>
                        <th scope="col">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($genres as $genre)
                        <tr>
                            <td class="number">{{ $genre['id'] }}</td>
                            <td class="name">{{ $genre['name'] }}</td>
                            <td>{{ $genre['description'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
