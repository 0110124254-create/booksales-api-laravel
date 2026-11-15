import { useCallback, useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api, { errorMessage } from '../services/api';
import { Notice, PageTitle } from '../components/Notice';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export default function BookDetailPage() {
    const { id } = useParams();
    const { user } = useAuth();
    const [book, setBook] = useState(null);
    const [reviews, setReviews] = useState([]);
    const [reviewMeta, setReviewMeta] = useState({ average_rating: 0, review_count: 0, can_review: false });
    const [rating, setRating] = useState(5);
    const [reviewBody, setReviewBody] = useState('');
    const [error, setError] = useState('');
    const [reviewError, setReviewError] = useState('');
    const [added, setAdded] = useState(false);
    const { add } = useCart();

    const loadReviews = useCallback(() => api.get(`/books/${id}/reviews`).then((response) => {
        setReviews(response.data.data ?? []);
        setReviewMeta(response.data.meta);
    }), [id]);

    useEffect(() => {
        api.get(`/books/${id}`).then((response) => setBook(response.data.data)).catch(() => setError('Buku tidak ditemukan atau tidak tersedia.'));
        loadReviews().catch((reason) => setReviewError(errorMessage(reason)));
    }, [id, loadReviews]);

    if (error) return <><Notice error>{error}</Notice><Link to="/" className="font-bold text-violet-900">Kembali ke katalog</Link></>;
    if (!book) return <p className="py-16 text-stone-500">Memuat detail buku…</p>;

    async function submitReview(event) {
        event.preventDefault();
        setReviewError('');
        try {
            await api.post(`/books/${id}/reviews`, { rating: Number(rating), body: reviewBody });
            setReviewBody('');
            await loadReviews();
        } catch (reason) {
            setReviewError(errorMessage(reason));
        }
    }

    return <>
        <div className="grid gap-10 md:grid-cols-[minmax(260px,420px)_1fr]">
            <div className="flex min-h-96 items-center justify-center rounded-[2rem] bg-violet-50 p-8">{book.cover ? <img src={`/${book.cover}`} alt={`Sampul ${book.title}`} className="max-h-[500px] max-w-full object-contain" /> : <span className="font-display text-3xl font-bold text-violet-950">{book.title}</span>}</div>
            <div className="py-3">
                <Link to="/" className="text-sm font-bold text-violet-800">← Kembali ke katalog</Link>
                <p className="mt-8 text-xs font-bold uppercase tracking-widest text-violet-800">{book.genre_record?.name}</p>
                <h1 className="mt-3 font-display text-4xl font-bold text-stone-950">{book.title}</h1>
                <p className="mt-3 text-lg text-stone-600">{book.author_record?.name}</p>
                <p className="mt-3 text-sm text-amber-700">{'★'.repeat(Math.round(reviewMeta.average_rating))}{'☆'.repeat(5 - Math.round(reviewMeta.average_rating))} <span className="text-stone-600">{reviewMeta.average_rating} dari 5 · {reviewMeta.review_count} ulasan</span></p>
                {book.isbn && <p className="mt-3 text-sm text-stone-500">ISBN: {book.isbn}</p>}
                <p className="mt-7 whitespace-pre-line leading-8 text-stone-700">{book.description || 'Belum ada deskripsi untuk buku ini.'}</p>
                <p className="mt-8 text-2xl font-black text-violet-950">{money.format(book.price)}</p>
                <p className="mt-2 text-sm text-stone-500">Stok tersedia: {book.stock}</p>
                <button onClick={() => { add(book); setAdded(true); }} disabled={!book.stock} className="mt-6 rounded-full bg-violet-950 px-7 py-3 font-bold text-white hover:bg-violet-800 disabled:bg-stone-400">Tambah ke keranjang</button>
                {added && <p role="status" className="mt-3 text-sm font-bold text-emerald-800">Buku ditambahkan ke keranjang.</p>}
            </div>
        </div>

        <section className="mt-14 grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(280px,360px)]">
            <div>
                <PageTitle eyebrow="Pendapat pembaca">Ulasan buku</PageTitle>
                <Notice error>{reviewError}</Notice>
                {reviews.length ? <div className="space-y-4">{reviews.map((review) => <article key={review.id} className="rounded-2xl border border-stone-200 bg-white p-5"><div className="flex items-center justify-between gap-3"><strong>{review.user?.name ?? 'Pembaca'}</strong><span className="text-amber-700">{'★'.repeat(review.rating)}{'☆'.repeat(5 - review.rating)}</span></div>{review.body && <p className="mt-3 whitespace-pre-line text-sm leading-6 text-stone-600">{review.body}</p>}<p className="mt-3 text-xs text-stone-400">{new Date(review.created_at).toLocaleDateString('id-ID')}</p></article>)}</div> : <p className="rounded-2xl border border-dashed border-stone-300 p-6 text-sm text-stone-500">Belum ada ulasan untuk buku ini.</p>}
            </div>
            <aside className="h-fit rounded-3xl border border-stone-200 bg-white p-6">
                <h2 className="text-lg font-black">Tulis ulasan</h2>
                {reviewMeta.can_review ? <form onSubmit={submitReview} className="mt-4 space-y-4"><div><label htmlFor="review-rating" className="block text-sm font-bold">Rating</label><select id="review-rating" value={rating} onChange={(event) => setRating(event.target.value)} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3">{[5, 4, 3, 2, 1].map((value) => <option value={value} key={value}>{value} bintang</option>)}</select></div><div><label htmlFor="review-body" className="block text-sm font-bold">Komentar</label><textarea id="review-body" rows="4" maxLength="2000" value={reviewBody} onChange={(event) => setReviewBody(event.target.value)} placeholder="Bagikan pengalaman Anda membaca buku ini" className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></div><button className="w-full rounded-full bg-violet-950 px-5 py-3 font-bold text-white">Kirim ulasan</button></form> : <p className="mt-3 text-sm leading-6 text-stone-600">Ulasan hanya tersedia bagi pelanggan yang sudah membeli buku ini dan pembayarannya terkonfirmasi. Setiap akun dapat mengulas satu kali.</p>}
                {!user && <Link to="/login" className="mt-4 inline-block text-sm font-bold text-violet-900">Masuk ke akun</Link>}
            </aside>
        </section>
    </>;
}
