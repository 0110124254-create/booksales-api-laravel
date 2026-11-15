import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Notice, PageTitle } from '../components/Notice';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import api, { errorMessage } from '../services/api';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export default function CheckoutPage() {
    const { user } = useAuth();
    const { items, total, clear } = useCart();
    const [fields, setFields] = useState({ customer_name: user?.name ?? '', customer_email: user?.email ?? '', customer_phone: '', shipping_address: '', payment_method: 'cash' });
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(false);
    const navigate = useNavigate();

    if (!items.length) return <><PageTitle>Checkout</PageTitle><p>Keranjang kosong. <Link to="/" className="font-bold text-violet-900">Pilih buku dulu.</Link></p></>;

    async function submit(event) {
        event.preventDefault();
        setBusy(true);
        setError('');
        try {
            const response = await api.post('/checkout', {
                ...fields,
                items: items.map((item) => ({ book_id: item.id, quantity: item.quantity })),
            });
            clear();
            navigate(`/orders/${response.data.data.reference}`);
        } catch (reason) {
            setError(errorMessage(reason));
        } finally {
            setBusy(false);
        }
    }

    return <div className="grid gap-8 lg:grid-cols-[1fr_360px]">
        <section className="rounded-[2rem] border border-stone-200 bg-white p-7 sm:p-9">
            <PageTitle eyebrow="Pemesanan">Informasi pengiriman</PageTitle>
            <Notice error>{error}</Notice>
            <form onSubmit={submit} className="space-y-5">
                <Field id="customer_name" label="Nama penerima" value={fields.customer_name} onChange={setFields} />
                <Field id="customer_email" label="Email" type="email" value={fields.customer_email} onChange={setFields} />
                <Field id="customer_phone" label="Nomor telepon" type="tel" value={fields.customer_phone} onChange={setFields} />
                <div><label htmlFor="shipping_address" className="block text-sm font-bold">Alamat pengiriman</label><textarea id="shipping_address" required maxLength={2000} rows="4" value={fields.shipping_address} onChange={(event) => setFields({ ...fields, shipping_address: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 outline-none focus:border-violet-700" /></div>
                <div><label htmlFor="payment_method" className="block text-sm font-bold">Metode pembayaran</label><select id="payment_method" value={fields.payment_method} onChange={(event) => setFields({ ...fields, payment_method: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3"><option value="cash">Bayar di tempat (COD)</option></select></div>
                <button disabled={busy} className="w-full rounded-full bg-violet-950 px-6 py-3.5 font-bold text-white disabled:opacity-50">{busy ? 'Membuat pesanan…' : 'Buat pesanan'}</button>
            </form>
        </section>
        <aside className="h-fit rounded-3xl border border-stone-200 bg-white p-6"><h2 className="text-lg font-black">Ringkasan</h2><div className="mt-4 space-y-3">{items.map((item) => <div key={item.id} className="flex justify-between gap-3 text-sm"><span>{item.title} × {item.quantity}</span><span>{money.format(item.price * item.quantity)}</span></div>)}</div><div className="mt-5 flex justify-between border-t border-stone-200 pt-4 font-black"><span>Total</span><span>{money.format(total)}</span></div></aside>
    </div>;
}

function Field({ id, label, type = 'text', value, onChange }) {
    return <div><label htmlFor={id} className="block text-sm font-bold">{label}</label><input id={id} type={type} required value={value} onChange={(event) => onChange({ ...value, [id]: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 outline-none focus:border-violet-700" /></div>;
}
