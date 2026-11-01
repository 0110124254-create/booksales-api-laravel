@extends('layouts.catalog')

@section('title', 'Tambah Buku')

@section('content')
    <section class="mx-auto max-w-3xl px-5 py-10 sm:px-8 sm:py-14">
        <a href="{{ route('admin.web-books.index') }}" class="text-sm font-bold text-emerald-800 hover:text-emerald-700">← Kembali ke daftar buku</a>
        <div class="mt-6">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Katalog Ruang Baca</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight text-stone-950">Tambah buku</h1>
            <p class="mt-3 text-sm leading-6 text-stone-600">Lengkapi detail buku untuk menambahkannya ke katalog.</p>
        </div>

        <form method="POST" action="{{ route('admin.web-books.store') }}" class="mt-8">
            @csrf
            @include('admin.books._form', ['submitLabel' => 'Simpan buku'])
        </form>
    </section>
@endsection
