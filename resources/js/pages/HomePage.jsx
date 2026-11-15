import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import api, { errorMessage } from '../services/api';
import BookCard from '../components/BookCard';
import { Notice, PageTitle } from '../components/Notice';
import { useCart } from '../contexts/CartContext';

export default function HomePage() {
    const [params, setParams] = useSearchParams();
    const [books, setBooks] = useState([]);
    const [genres, setGenres] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [error, setError] = useState('');
    const { add } = useCart();
    const query = params.get('q') ?? '';
    const genreId = params.get('genre_id') ?? '';
    const page = params.get('page') ?? '1';

    useEffect(() => {
        api.get('/genres').then((response) => setGenres(response.data.data ?? [])).catch(() => setGenres([]));
    }, []);

    useEffect(() => {
        api.get('/books', { params: { q: query || undefined, genre_id: genreId || undefined, page } })
            .then((response) => {
                setBooks(response.data.data ?? []);
                setMeta(response.data.meta ?? { current_page: 1, last_page: 1, total: 0 });
                setError('');
            })
            .catch((reason) => setError(errorMessage(reason)));
    }, [query, genreId, page]);

    function setFilter(key, value) {
        const next = new URLSearchParams(params);
        if (value) next.set(key, value); else next.delete(key);
        if (key !== 'page') next.delete('page');
        setParams(next);
    }

    return <>
        <section className="mb-12 overflow-hidden rounded-[2rem] bg-violet-950 px-7 py-12 text-white sm:px-12 sm:py-16">
            <p className="text-sm font-bold uppercase tracking-[0.2em] text-violet-200">Ruang Baca</p>
            <h1 className="mt-4 max-w-3xl font-display text-4xl font-bold leading-tight sm:text-6xl">Temukan buku untuk cerita berikutnya.</h1>
            <p className="mt-5 max-w-xl leading-7 text-violet-100">Jelajahi koleksi pilihan, kenali penulis, dan pesan buku favorit Anda.</p>
        </section>
        <div className="flex flex-wrap items-end justify-between gap-4">
            <PageTitle eyebrow="Katalog buku" description={`${meta.total} buku tersedia untuk Anda jelajahi.`}>Pilihan pembaca</PageTitle>
            <label className="mb-8 text-sm font-bold text-stone-700">Kategori
                <select value={genreId} onChange={(event) => setFilter('genre_id', event.target.value)} className="ml-3 rounded-full border border-stone-300 bg-white px-4 py-2">
                    <option value="">Semua kategori</option>
                    {genres.map((genre) => <option key={genre.id} value={genre.id}>{genre.name}</option>)}
                </select>
            </label>
        </div>
        <Notice error>{error}</Notice>
        {books.length ? <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">{books.map((book) => <BookCard key={book.id} book={book} onAdd={add} />)}</div> : <p className="rounded-3xl border border-dashed border-stone-300 p-10 text-center text-stone-500">Tidak ada buku yang cocok dengan pencarian.</p>}
        {meta.last_page > 1 && <div className="mt-8 flex items-center justify-center gap-4">
            <button disabled={meta.current_page <= 1} onClick={() => setFilter('page', String(meta.current_page - 1))} className="rounded-full border border-stone-300 px-5 py-2 font-bold disabled:opacity-40">Sebelumnya</button>
            <span className="text-sm text-stone-600">Halaman {meta.current_page} dari {meta.last_page}</span>
            <button disabled={meta.current_page >= meta.last_page} onClick={() => setFilter('page', String(meta.current_page + 1))} className="rounded-full border border-stone-300 px-5 py-2 font-bold disabled:opacity-40">Berikutnya</button>
        </div>}
    </>;
}
