import { useCallback, useEffect, useState } from 'react';
import AdminNav from '../../components/AdminNav';
import { Notice, PageTitle } from '../../components/Notice';
import api, { errorMessage } from '../../services/api';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
const statuses = ['new', 'processing', 'shipped', 'completed'];

export default function AdminOrdersPage() {
    const [orders, setOrders] = useState([]);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const reload = useCallback(() => api.get('/admin/sales').then((response) => setOrders(response.data.data.data ?? [])), []);
    useEffect(() => { reload().catch((reason) => setError(errorMessage(reason))); }, [reload]);

    async function update(order, fulfillment_status) {
        setError('');
        try { await api.patch(`/admin/sales/${order.id}`, { fulfillment_status }); setNotice(`Status ${order.reference} diperbarui.`); await reload(); }
        catch (reason) { setError(errorMessage(reason)); }
    }

    async function confirmPayment(order) {
        setError('');
        try { await api.post(`/admin/sales/${order.id}/paid`); setNotice(`Pembayaran ${order.reference} dikonfirmasi.`); await reload(); }
        catch (reason) { setError(errorMessage(reason)); }
    }

    return <><AdminNav /><PageTitle eyebrow="Manajemen">Pesanan</PageTitle><Notice>{notice}</Notice><Notice error>{error}</Notice>
        <div className="space-y-4">{orders.map((order) => <article key={order.id} className="rounded-3xl border border-stone-200 bg-white p-6">
            <div className="flex flex-wrap justify-between gap-4"><div><h2 className="font-black">{order.reference}</h2><p className="mt-1 text-sm text-stone-500">{order.user?.name ?? order.customer_name} · {order.customer_phone} · {order.shipping_address}</p>{order.shipping_courier && <p className="mt-1 text-sm text-violet-800">{order.shipping_courier} {order.shipping_service} · ongkir {money.format(order.shipping_cost)}</p>}</div><div className="text-right"><strong>{money.format(order.total)}</strong><p className="mt-1 text-sm capitalize text-violet-800">{order.payment_method} · {order.payment_status}</p></div></div>
            <ul className="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-sm text-stone-600">{order.items?.map((item) => <li key={item.id}>{item.book_title} × {item.quantity}</li>)}</ul>
            <div className="mt-5 flex flex-wrap items-center gap-3 border-t border-stone-100 pt-4"><label className="text-sm font-bold">Status pengiriman <select value={order.fulfillment_status} onChange={(event) => update(order, event.target.value)} className="ml-2 rounded-xl border border-stone-300 px-3 py-2">{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select></label>{order.payment_status === 'pending' && order.payment_method === 'cash' && <button onClick={() => confirmPayment(order)} className="rounded-full bg-emerald-700 px-4 py-2 text-sm font-bold text-white">Konfirmasi bayar</button>}</div>
        </article>)}</div>
    </>;
}
