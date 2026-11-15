@extends('layouts.catalog')
@section('title', $genre->exists ? 'Edit Kategori' : 'Tambah Kategori')
@section('content')
<section class="mx-auto max-w-2xl px-5 py-12 sm:px-8"><div class="rounded-3xl border border-stone-200 bg-white p-7 sm:p-9"><a href="{{ route('admin.web-genres.index') }}" class="text-sm font-bold text-violet-900">&larr; Kembali</a><h1 class="mt-4 text-3xl font-black">{{ $genre->exists ? 'Edit kategori' : 'Tambah kategori' }}</h1>
    @if ($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form method="POST" action="{{ $genre->exists ? route('admin.web-genres.update', $genre) : route('admin.web-genres.store') }}" class="mt-7 space-y-5">@csrf @if ($genre->exists)@method('PUT')@endif
        <div><label for="name" class="text-sm font-bold">Nama kategori</label><input id="name" name="name" required maxlength="255" value="{{ old('name', $genre->name) }}" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3"></div>
        <div><label for="description" class="text-sm font-bold">Deskripsi</label><textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3">{{ old('description', $genre->description) }}</textarea></div>
        <button class="rounded-full bg-violet-950 px-6 py-3 font-bold text-white">Simpan kategori</button>
    </form>
</div></section>
@endsection
