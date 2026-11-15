<a href="{{ route('home') }}" @class(['is-current' => request()->routeIs('home', 'books.catalog')])>Beranda</a>
<a href="{{ route('catalog.genres.index') }}" @class(['is-current' => request()->routeIs('catalog.genres.*')])>Genre</a>
<a href="{{ route('catalog.authors.index') }}" @class(['is-current' => request()->routeIs('catalog.authors.*')])>Penulis</a>
<a href="{{ route('about') }}">Tentang</a>
<a href="{{ route('contact.create') }}">Kontak</a>
@auth
    @can('access-admin')
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.web-genres.index') }}">Kategori</a>
        <a href="{{ route('admin.users.index') }}">User</a>
        <a href="{{ route('admin.sales.index') }}">Pesanan</a>
        <a href="{{ route('admin.contacts.index') }}">Pesan</a>
    @endcan
    @can('access-customer')
        <a href="{{ route('orders.index') }}">Pesanan</a>
    @endcan
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
@else
    <a href="{{ route('login') }}">Masuk</a>
    <a href="{{ route('register') }}">Daftar</a>
@endauth
