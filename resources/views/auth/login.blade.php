@extends('layouts.catalog')

@section('title', 'Login Admin')

@section('content')
    <p class="eyebrow">Area admin</p>
    <h1>Login Admin</h1>
    <p class="intro">Masuk untuk mengelola buku, pesanan, dan laporan penjualan.</p>

    <form class="form-card" method="POST" action="{{ route('login.store') }}">
        @csrf
        <label class="field">
            Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </label>
        <label class="field">
            Kata sandi
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <label class="checkbox-field">
            <input type="checkbox" name="remember" value="1">
            Ingat saya
        </label>
        <button type="submit">Masuk</button>
    </form>
@endsection
