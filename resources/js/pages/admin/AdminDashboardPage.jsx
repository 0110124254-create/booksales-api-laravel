import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import AdminNav from '../../components/AdminNav';
import { Notice, PageTitle } from '../../components/Notice';
import api, { errorMessage } from '../../services/api';

const metrics = [['book_count', 'Buku'], ['user_count', 'User'], ['sale_count', 'Pesanan'], ['unread_message_count', 'Pesan baru']];

export default function AdminDashboardPage() {
    const [data, setData] = useState(null);
    const [error, setError] = useState('');
    useEffect(() => { api.get('/admin/dashboard').then((response) => setData(response.data.data)).catch((reason) => setError(errorMessage(reason))); }, []);
    return <><AdminNav /><PageTitle eyebrow="Panel admin">Ringkasan toko</PageTitle><Notice error>{error}</Notice>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">{metrics.map(([key, label]) => <article key={key} className="rounded-3xl border border-stone-200 bg-white p-6"><p className="text-sm text-stone-500">{label}</p><p className="mt-2 text-3xl font-black text-violet-950">{data?.[key] ?? '—'}</p></article>)}</div>
        <section className="mt-8 rounded-3xl border border-stone-200 bg-white p-6"><div className="flex items-center justify-between"><h2 className="text-lg font-black">Pesanan terbaru</h2><Link to="/admin/orders" className="font-bold text-violet-900">Lihat semua</Link></div>
            <div className="mt-4 divide-y divide-stone-100">{data?.recent_sales?.length ? data.recent_sales.map((sale) => <div key={sale.id} className="flex justify-between gap-3 py-3 text-sm"><span>{sale.reference} · {sale.user?.name ?? sale.customer_name}</span><span className="capitalize text-stone-500">{sale.payment_status}</span></div>) : <p className="py-4 text-sm text-stone-500">Belum ada pesanan.</p>}</div>
        </section>
    </>;
}
