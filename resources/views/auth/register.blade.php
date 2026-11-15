@extends('layouts.catalog')

@section('title', 'Daftar Akun')

@section('content')
    <section class="mx-auto grid min-h-[calc(100vh-180px)] max-w-7xl items-center gap-10 px-5 py-12 sm:px-8 sm:py-16 lg:grid-cols-2 lg:gap-20">
        <div class="max-w-xl">
            <p class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-violet-900">Ruang Baca · Akun</p>
            <h1 class="mt-6 text-4xl font-black leading-tight tracking-tight text-stone-950 sm:text-5xl">Mulai perjalanan membaca.</h1>
            <p class="mt-5 text-base leading-7 text-stone-600 sm:text-lg sm:leading-8">Buat akun untuk memesan buku dan melihat pesanan Anda.</p>
            <a href="{{ route('home') }}" class="mt-7 inline-flex items-center gap-2 text-sm font-bold text-violet-900 hover:text-violet-700">&larr; Kembali ke katalog</a>
        </div>

        <div class="mx-auto w-full max-w-lg rounded-[2rem] border border-stone-200/80 bg-white p-7 shadow-2xl shadow-stone-900/[0.07] sm:p-10">
            <h2 class="text-2xl font-black text-stone-950">Buat akun user</h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">Isi data berikut untuk mendaftar sebagai pengguna.</p>

            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-bold text-stone-800">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 text-stone-950 outline-none focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10">
                </div>
                <div>
                    <label for="username" class="block text-sm font-bold text-stone-800">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="username" class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 text-stone-950 outline-none focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10">
                </div>
                <div>
                    <label for="email" class="block text-sm font-bold text-stone-800">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 text-stone-950 outline-none focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10">
                </div>
                <div>
                    <label for="password" class="block text-sm font-bold text-stone-800">Kata sandi</label>
                    <div class="relative mt-2">
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 pr-24 text-stone-950 outline-none focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10" data-password-input>
                        <button type="button" class="absolute inset-y-0 right-3 my-auto rounded-lg px-2 text-sm font-bold text-violet-900 hover:bg-violet-50 focus:outline-none focus:ring-2 focus:ring-violet-700" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Tampilkan kata sandi">Tampilkan</button>
                    </div>
                    <p class="mt-1 text-xs text-stone-500">Minimal 6 karakter.</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-stone-800">Konfirmasi kata sandi</label>
                    <div class="relative mt-2">
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 pr-24 text-stone-950 outline-none focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10" data-password-input>
                        <button type="button" class="absolute inset-y-0 right-3 my-auto rounded-lg px-2 text-sm font-bold text-violet-900 hover:bg-violet-50 focus:outline-none focus:ring-2 focus:ring-violet-700" data-password-toggle aria-controls="password_confirmation" aria-pressed="false" aria-label="Tampilkan konfirmasi kata sandi">Tampilkan</button>
                    </div>
                </div>
                <button type="submit" class="w-full rounded-full bg-violet-950 px-6 py-3.5 text-sm font-bold text-white shadow-md transition hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-700/20">Daftar</button>
            </form>
            <p class="mt-6 text-center text-sm text-stone-600">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-violet-900 hover:underline">Masuk</a></p>
        </div>
    </section>
@endsection
