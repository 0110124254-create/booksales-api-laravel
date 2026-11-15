@extends('layouts.catalog')
@section('title', 'Daftar User')
@section('content')
<section class="mx-auto max-w-7xl px-5 py-12 sm:px-8"><p class="text-xs font-bold uppercase tracking-wider text-violet-800">Admin</p><h1 class="mt-2 text-4xl font-black">User terdaftar</h1>
    <div class="mt-7 overflow-x-auto rounded-2xl border border-stone-200 bg-white"><table class="w-full text-left text-sm"><thead class="bg-stone-50"><tr><th class="p-4">Nama</th><th class="p-4">Username</th><th class="p-4">Email</th><th class="p-4">Jumlah pesanan</th><th class="p-4">Terdaftar</th></tr></thead><tbody class="divide-y divide-stone-100">@forelse ($users as $user)<tr><td class="p-4 font-bold">{{ $user->name }}</td><td class="p-4">{{ $user->username }}</td><td class="p-4">{{ $user->email }}</td><td class="p-4">{{ $user->sales_count }}</td><td class="p-4">{{ $user->created_at->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-stone-500">Belum ada user terdaftar.</td></tr>@endforelse</tbody></table></div><div class="mt-5">{{ $users->links() }}</div>
</section>
@endsection
