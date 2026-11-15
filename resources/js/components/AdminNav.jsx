import { NavLink } from 'react-router-dom';

const items = [
    ['/admin', 'Ringkasan'],
    ['/admin/books', 'Buku'],
    ['/admin/genres', 'Genre'],
    ['/admin/authors', 'Penulis'],
    ['/admin/users', 'Pengguna'],
    ['/admin/orders', 'Pesanan'],
    ['/admin/messages', 'Pesan'],
];

export default function AdminNav() {
    return <nav aria-label="Navigasi admin" className="mb-8 flex flex-wrap gap-2 rounded-2xl bg-violet-950 p-2">
        {items.map(([path, label]) => <NavLink key={path} to={path} end={path === '/admin'} className={({ isActive }) => `rounded-xl px-4 py-2 text-sm font-bold ${isActive ? 'bg-white text-violet-950' : 'text-violet-100 hover:bg-violet-900'}`}>{label}</NavLink>)}
    </nav>;
}
