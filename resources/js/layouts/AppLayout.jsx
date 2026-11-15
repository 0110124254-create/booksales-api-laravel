import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';

const linkClass = ({ isActive }) => `rounded-full px-3 py-2 text-sm font-bold ${isActive ? 'bg-violet-100 text-violet-950' : 'text-stone-600 hover:bg-stone-100'}`;

export default function AppLayout() {
    const { user, isAdmin, logout } = useAuth();
    const { count } = useCart();
    const [query, setQuery] = useState('');
    const navigate = useNavigate();

    async function handleLogout() {
        await logout();
        navigate('/');
    }

    function search(event) {
        event.preventDefault();
        navigate(`/?q=${encodeURIComponent(query.trim())}`);
    }

    return (
        <div className="min-h-screen bg-[#fbfaf8] text-stone-900">
            <header className="border-b border-stone-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-5 py-4 sm:px-8">
                    <Link to="/" className="mr-auto font-display text-2xl font-bold text-violet-950">ruang<span className="text-violet-600">baca</span></Link>
                    <form onSubmit={search} className="order-3 flex w-full rounded-full border border-stone-200 bg-stone-50 p-1 sm:order-none sm:w-auto">
                        <label htmlFor="site-search" className="sr-only">Cari judul, penulis, atau ISBN</label>
                        <input id="site-search" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Cari buku atau penulis…" className="min-w-0 flex-1 rounded-full bg-transparent px-4 py-2 text-sm outline-none sm:w-64" />
                        <button className="rounded-full bg-violet-950 px-4 text-sm font-bold text-white">Cari</button>
                    </form>
                    <Link to="/cart" className="rounded-full px-3 py-2 text-sm font-bold text-violet-950 hover:bg-violet-50">Keranjang ({count})</Link>
                    {user
                        ? <>
                            <span className="hidden text-sm text-stone-500 md:inline">{user.name}</span>
                            <button type="button" onClick={handleLogout} className="rounded-full border border-stone-300 px-4 py-2 text-sm font-bold text-stone-700 hover:bg-stone-50">Keluar</button>
                        </>
                        : <Link to="/login" className="rounded-full bg-violet-950 px-4 py-2 text-sm font-bold text-white">Masuk</Link>}
                </div>
                <nav className="mx-auto flex max-w-7xl flex-wrap gap-1 px-5 pb-3 sm:px-8" aria-label="Navigasi utama">
                    <NavLink to="/" end className={linkClass}>Beranda</NavLink>
                    <NavLink to="/about" className={linkClass}>Tentang</NavLink>
                    {user && !isAdmin && <>
                        <NavLink to="/account/orders" className={linkClass}>Pesanan saya</NavLink>
                        <NavLink to="/contact" className={linkClass}>Hubungi admin</NavLink>
                    </>}
                    {isAdmin && <NavLink to="/admin" className={linkClass}>Panel admin</NavLink>}
                    {!user && <NavLink to="/register" className={linkClass}>Daftar</NavLink>}
                </nav>
            </header>
            <main className="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12"><Outlet /></main>
            <footer className="border-t border-stone-200 bg-white px-5 py-8 text-center text-sm text-stone-500">Ruang Baca · Temukan cerita berikutnya.</footer>
        </div>
    );
}
