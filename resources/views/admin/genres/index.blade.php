@extends('layouts.catalog')
@section('title', 'Kelola Kategori')
@section('content')
<section class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
    <div class="flex items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-wider text-violet-800">Admin</p><h1 class="mt-2 text-4xl font-black">Kategori buku</h1></div><a href="{{ route('admin.web-genres.create') }}" class="rounded-full bg-violet-950 px-5 py-3 text-sm font-bold text-white">Tambah kategori</a></div>
    <div class="mt-7 overflow-x-auto rounded-2xl border border-stone-200 bg-white"><table class="w-full text-left text-sm"><thead class="bg-stone-50"><tr><th class="p-4">Kategori</th><th class="p-4">Buku</th><th class="p-4">Aksi</th></tr></thead><tbody class="divide-y divide-stone-100">@foreach ($genres as $genre)<tr><td class="p-4"><strong>{{ $genre->name }}</strong><p class="mt-1 text-stone-500">{{ $genre->description }}</p></td><td class="p-4">{{ $genre->books_count }}</td><td class="p-4"><div class="flex gap-3"><a class="font-bold text-violet-900" href="{{ route('admin.web-genres.edit', $genre) }}">Edit</a><form method="POST" action="{{ route('admin.web-genres.destroy', $genre) }}" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')<button class="font-bold text-red-700">Hapus</button></form></div></td></tr>@endforeach</tbody></table></div>
    <div class="mt-5">{{ $genres->links() }}</div>
</section>
@endsection
