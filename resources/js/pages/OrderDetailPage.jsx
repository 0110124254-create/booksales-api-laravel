import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api, { errorMessage } from '../services/api';
import { Notice, PageTitle } from '../components/Notice';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export default function OrderDetailPage() {
    const { reference } = useParams();
    const [order, setOrder] = useState(null);
    const [error, setError] = useState('');
    useEffect(() => { api.get(`/orders/${reference}`).then((response) => setOrder(response.data.data)).catch((reason) => setError(errorMessage(reason))); }, [reference]);
    if (error) return <Notice error>{error}</Notice>;
    if (!order) return <p className="py-12 text-stone-500">Memuat pesanan…</p>;

    return <>
        <PageTitle eyebrow="Detail pesanan">{order.reference}</PageTitle>
        <Notice>Pembayaran: {order.payment_method === 'cash' ? 'Bayar di tempat (COD)' : 'Midtrans'} · Status: {order.payment_status}</Notice>
        {order.payment_method === 'midtrans' && order.payment_status === 'pending' && order.midtrans_redirect_url && <a href={order.midtrans_redirect_url} className="mb-6 inline-flex rounded-full bg-violet-950 px-6 py-3 font-bold text-white">Lanjutkan pembayaran</a>}
        <div className="grid gap-7 lg:grid-cols-[1fr_320px]">
            <section className="rounded-3xl border border-stone-200 bg-white p-6"><h2 className="text-lg font-black">Buku yang dipesan</h2><div className="mt-4 divide-y divide-stone-100">{order.items?.map((item) => <div key={item.id} className="flex justify-between gap-4 py-4"><div><strong>{item.book_title}</strong><p className="mt-1 text-sm text-stone-500">{item.quantity} × {money.format(item.unit_price)}</p></div><strong>{money.format(item.quantity * item.unit_price)}</strong></div>)}</div></section>
            <aside className="h-fit rounded-3xl border border-stone-200 bg-white p-6"><h2 className="font-black">Pengiriman</h2><p className="mt-3 whitespace-pre-line text-sm leading-6 text-stone-600">{order.customer_name}<br />{order.customer_phone}<br />{order.shipping_address}</p>{order.shipping_courier && <p className="mt-4 text-sm text-stone-600">{order.shipping_courier} {order.shipping_service}{order.shipping_etd ? ` · ${order.shipping_etd}` : ''}</p>}<div className="mt-5 space-y-2 border-t pt-4 text-sm"><div className="flex justify-between"><span>Subtotal</span><span>{money.format(order.subtotal ?? order.total)}</span></div><div className="flex justify-between"><span>Ongkos kirim</span><span>{money.format(order.shipping_cost ?? 0)}</span></div></div><div className="mt-4 flex justify-between border-t pt-4 font-black"><span>Total</span><span>{money.format(order.total)}</span></div></aside>
        </div>
        <Link to="/account/orders" className="mt-6 inline-flex font-bold text-violet-900">Lihat semua pesanan</Link>
    </>;
}
