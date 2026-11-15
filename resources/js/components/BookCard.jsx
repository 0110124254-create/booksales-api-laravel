import { Link } from 'react-router-dom';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export default function BookCard({ book, onAdd }) {
    return (
        <article className="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <Link to={`/books/${book.id}`} className="block bg-violet-50 p-5">
                <div className="flex h-64 items-center justify-center overflow-hidden rounded-2xl bg-white">
                    {book.cover
                        ? <img src={`/${book.cover}`} alt={`Sampul ${book.title}`} className="h-full w-full object-contain" />
                        : <span className="px-6 text-center font-display text-xl font-bold text-violet-900">{book.title}</span>}
                </div>
            </Link>
            <div className="space-y-3 p-5">
                <p className="text-xs font-bold uppercase tracking-widest text-violet-800">{book.genre_record?.name ?? 'Tanpa genre'}</p>
                <h2 className="line-clamp-2 min-h-12 text-lg font-black text-stone-950"><Link to={`/books/${book.id}`}>{book.title}</Link></h2>
                <p className="text-sm text-stone-500">{book.author_record?.name ?? 'Penulis'}</p>
                <div className="flex items-center justify-between gap-3">
                    <strong className="text-violet-950">{money.format(book.price)}</strong>
                    <button type="button" onClick={() => onAdd?.(book)} disabled={!book.stock} className="rounded-full bg-violet-950 px-4 py-2 text-sm font-bold text-white hover:bg-violet-800 disabled:cursor-not-allowed disabled:bg-stone-400">{book.stock ? 'Tambah' : 'Habis'}</button>
                </div>
            </div>
        </article>
    );
}
