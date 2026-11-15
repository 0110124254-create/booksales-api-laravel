import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api, { errorMessage } from '../services/api';
import { Notice, PageTitle } from '../components/Notice';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export default function OrdersPage() {
    const [orders, setOrders] = useState([]);
    const [error, setError] = useState('');
    useEffect(() => { api.get('/my/orders').then((response) => setOrders(response.data.data.data ?? [])).catch((reason) => setError(errorMessage(reason))); }, []);

    return <>
        <PageTitle eyebrow="Akun user">Pesanan saya</PageTitle>
        <Notice error>{error}</Notice>
        {orders.length ? <div className="space-y-4">{orders.map((order) => <Link key={order.id} to={`/orders/${order.reference}`} className="flex flex-wrap justify-between gap-4 rounded-3xl border border-stone-200 bg-white p-6 hover:border-violet-300"><div><strong>{order.reference}</strong><p className="mt-2 text-sm text-stone-500">{new Date(order.created_at).toLocaleString('id-ID')} · {order.items?.length ?? 0} jenis buku</p></div><div className="text-right"><strong>{money.format(order.total)}</strong><p className="mt-2 text-sm capitalize text-violet-800">Pembayaran: {order.payment_status}</p></div></Link>)}</div> : !error && <p className="rounded-3xl border border-dashed border-stone-300 p-10 text-center text-stone-500">Belum ada pesanan.</p>}
    </>;
}
