@extends('layouts.catalog')

@section('title', 'Login')

@section('content')
    <section class="mx-auto grid min-h-[calc(100vh-180px)] max-w-7xl items-center gap-10 px-5 py-12 sm:px-8 sm:py-16 lg:grid-cols-2 lg:gap-20">
        <div class="max-w-xl">
            <p class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-violet-900">Login Admin &amp; User</p>
            <h1 class="mt-6 text-4xl font-black leading-tight tracking-tight text-stone-950 sm:text-5xl">Selamat datang kembali.</h1>
            <p class="mt-5 text-base leading-7 text-stone-600 sm:text-lg sm:leading-8">Masuk ke akun Ruang Baca Anda.</p>
            <a href="{{ route('home') }}" class="mt-7 inline-flex items-center gap-2 text-sm font-bold text-violet-900 hover:text-violet-700"><span aria-hidden="true">←</span> Kembali ke katalog</a>
        </div>

        <div class="mx-auto w-full max-w-lg rounded-[2rem] border border-stone-200/80 bg-white p-7 shadow-2xl shadow-stone-900/[0.07] sm:p-10">
            <div>
                <p class="text-sm font-bold text-violet-800">Ruang Baca</p>
                <h2 class="mt-2 text-2xl font-black text-stone-950">Masuk ke akun</h2>
                <p class="mt-2 text-sm leading-6 text-stone-600">Gunakan email dan kata sandi akun Anda.</p>
            </div>

            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-bold text-stone-800">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 text-stone-950 outline-none transition placeholder:text-stone-400 focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-stone-800">Kata sandi</label>
                    <div class="relative mt-2">
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 pr-24 text-stone-950 outline-none transition placeholder:text-stone-400 focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10" data-password-input>
                        <span data-password-toggle-root="password"></span>
                    </div>
                </div>

                <label class="flex items-center gap-3 text-sm text-stone-600">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-stone-300 accent-violet-900">
                    Ingat saya
                </label>

                <button type="submit" class="w-full rounded-full bg-violet-950 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-violet-950/10 transition hover:-translate-y-0.5 hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-700/20">Masuk</button>
            </form>
            <p class="mt-6 text-center text-sm text-stone-600">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-violet-900 hover:underline">Daftar sekarang</a></p>
        </div>
    </section>
@endsection
