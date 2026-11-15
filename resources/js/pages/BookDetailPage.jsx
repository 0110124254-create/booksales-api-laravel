import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../services/api';
import { Notice } from '../components/Notice';
import { useCart } from '../contexts/CartContext';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export default function BookDetailPage() {
    const { id } = useParams();
    const [book, setBook] = useState(null);
    const [error, setError] = useState('');
    const [added, setAdded] = useState(false);
    const { add } = useCart();

    useEffect(() => { api.get(`/books/${id}`).then((response) => setBook(response.data.data)).catch(() => setError('Buku tidak ditemukan atau tidak tersedia.')); }, [id]);

    if (error) return <><Notice error>{error}</Notice><Link to="/" className="font-bold text-violet-900">Kembali ke katalog</Link></>;
    if (!book) return <p className="py-16 text-stone-500">Memuat detail buku…</p>;

    return <div className="grid gap-10 md:grid-cols-[minmax(260px,420px)_1fr]">
        <div className="flex min-h-96 items-center justify-center rounded-[2rem] bg-violet-50 p-8">{book.cover ? <img src={`/${book.cover}`} alt={`Sampul ${book.title}`} className="max-h-[500px] max-w-full object-contain" /> : <span className="font-display text-3xl font-bold text-violet-950">{book.title}</span>}</div>
        <div className="py-3">
            <Link to="/" className="text-sm font-bold text-violet-800">← Kembali ke katalog</Link>
            <p className="mt-8 text-xs font-bold uppercase tracking-widest text-violet-800">{book.genre_record?.name}</p>
            <h1 className="mt-3 font-display text-4xl font-bold text-stone-950">{book.title}</h1>
            <p className="mt-3 text-lg text-stone-600">{book.author_record?.name}</p>
            {book.isbn && <p className="mt-3 text-sm text-stone-500">ISBN: {book.isbn}</p>}
            <p className="mt-7 whitespace-pre-line leading-8 text-stone-700">{book.description || 'Belum ada deskripsi untuk buku ini.'}</p>
            <p className="mt-8 text-2xl font-black text-violet-950">{money.format(book.price)}</p>
            <p className="mt-2 text-sm text-stone-500">Stok tersedia: {book.stock}</p>
            <button onClick={() => { add(book); setAdded(true); }} disabled={!book.stock} className="mt-6 rounded-full bg-violet-950 px-7 py-3 font-bold text-white hover:bg-violet-800 disabled:bg-stone-400">Tambah ke keranjang</button>
            {added && <p role="status" className="mt-3 text-sm font-bold text-emerald-800">Buku ditambahkan ke keranjang.</p>}
        </div>
    </div>;
}
