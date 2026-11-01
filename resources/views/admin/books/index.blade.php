@extends('layouts.catalog')

@section('title', 'Kelola Buku')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-emerald-800 hover:text-emerald-700">← Dashboard</a>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Area administrator</p>
                <h1 class="mt-2 text-4xl font-black tracking-tight text-stone-950">Kelola buku</h1>
                <p class="mt-3 text-sm leading-6 text-stone-600">Tambah, perbarui, atau hapus buku dari katalog Ruang Baca.</p>
            </div>
            <a href="{{ route('admin.web-books.create') }}" class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-950 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">
                <span aria-hidden="true">+</span> Tambah buku
            </a>
        </div>

        <div class="mt-8 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
            @if ($books->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-lg font-bold text-stone-900">Belum ada buku di katalog.</p>
                    <p class="mt-2 text-sm text-stone-600">Tambahkan buku pertama untuk mulai mengisi koleksi.</p>
                    <a href="{{ route('admin.web-books.create') }}" class="mt-5 inline-flex rounded-full bg-emerald-950 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Tambah buku</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Buku</th>
                                <th scope="col" class="px-6 py-4 font-bold">Genre</th>
                                <th scope="col" class="px-6 py-4 font-bold">Harga / Stok</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status</th>
                                <th scope="col" class="px-6 py-4 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($books as $book)
                                <tr class="transition hover:bg-stone-50/80">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-stone-950">{{ $book->title }}</p>
                                        <p class="mt-1 text-xs text-stone-500">Oleh {{ $book->authorRecord?->name ?? $book->author }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-stone-600">{{ $book->genre }}</td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-stone-900">Rp{{ number_format($book->price, 0, ',', '.') }}</p>
                                        <p class="mt-1 text-xs text-stone-500">{{ $book->stock }} tersedia</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span @class(['rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-800' => $book->is_active, 'bg-stone-100 text-stone-600' => ! $book->is_active])>
                                            {{ $book->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.web-books.edit', $book) }}" class="rounded-full border border-stone-200 px-4 py-2 text-xs font-bold text-stone-700 transition hover:border-emerald-700 hover:text-emerald-900">Edit</a>
                                            <form method="POST" action="{{ route('admin.web-books.destroy', $book) }}" onsubmit="return confirm('Hapus buku ini dari katalog?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-red-200 px-4 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($books->hasPages())
                    <div class="border-t border-stone-100 px-6 py-4">{{ $books->links() }}</div>
                @endif
            @endif
        </div>
    </section>
@endsection
