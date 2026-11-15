import { useEffect, useMemo, useState } from 'react';
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
    const [options, setOptions] = useState(null);
    const [destinationQuery, setDestinationQuery] = useState('');
    const [destinations, setDestinations] = useState([]);
    const [destination, setDestination] = useState(null);
    const [quotes, setQuotes] = useState([]);
    const [selectedQuote, setSelectedQuote] = useState(null);
    const [loadingQuotes, setLoadingQuotes] = useState(false);
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(false);
    const navigate = useNavigate();
    const quoteItems = useMemo(() => items.map((item) => ({ book_id: item.id, quantity: item.quantity })), [items]);

    useEffect(() => {
        api.get('/checkout/options')
            .then((response) => setOptions(response.data.data))
            .catch((reason) => {
                setOptions({ midtrans_enabled: false, shipping_enabled: false, checkout_enabled: false });
                setError(errorMessage(reason));
            });
    }, []);

    useEffect(() => {
        if (!options?.shipping_enabled || destinationQuery.trim().length < 3 || destination?.label === destinationQuery) {
            setDestinations([]);
            return undefined;
        }

        let active = true;
        const timer = window.setTimeout(() => {
            api.get('/shipping/destinations', { params: { q: destinationQuery.trim() } })
                .then((response) => { if (active) setDestinations(response.data.data ?? []); })
                .catch((reason) => { if (active) setError(errorMessage(reason)); });
        }, 350);

        return () => { active = false; window.clearTimeout(timer); };
    }, [destinationQuery, destination, options?.shipping_enabled]);

    if (!items.length) return <><PageTitle>Checkout</PageTitle><p>Keranjang kosong. <Link to="/" className="font-bold text-violet-900">Pilih buku dulu.</Link></p></>;

    async function calculateShipping() {
        if (!destination) return;
        setLoadingQuotes(true);
        setError('');
        setQuotes([]);
        setSelectedQuote(null);
        try {
            const response = await api.post('/shipping/quotes', { destination_id: destination.id, items: quoteItems });
            const availableQuotes = response.data.data ?? [];
            setQuotes(availableQuotes);
            if (availableQuotes.length === 1) setSelectedQuote(availableQuotes[0]);
        } catch (reason) {
            setError(errorMessage(reason));
        } finally {
            setLoadingQuotes(false);
        }
    }

    async function submit(event) {
        event.preventDefault();
        if (!options) return;
        if (options?.shipping_enabled && (!destination || !selectedQuote)) {
            setError('Pilih alamat tujuan dan layanan kurir terlebih dahulu.');
            return;
        }

        setBusy(true);
        setError('');
        try {
            const response = await api.post('/checkout', {
                ...fields,
                ...(options?.shipping_enabled ? {
                    shipping_destination_id: destination.id,
                    shipping_destination: destination.label,
                    shipping_courier: selectedQuote.code,
                    shipping_service: selectedQuote.service,
                } : {}),
                items: quoteItems,
            });
            clear();
            const order = response.data.data;
            if (order.payment_redirect_url) {
                window.location.assign(order.payment_redirect_url);
                return;
            }
            navigate(`/orders/${order.reference}`);
        } catch (reason) {
            setError(errorMessage(reason));
            setBusy(false);
        }
    }

    const shippingCost = selectedQuote?.cost ?? 0;

    return <div className="grid gap-8 lg:grid-cols-[1fr_360px]">
        <section className="rounded-[2rem] border border-stone-200 bg-white p-7 sm:p-9">
            <PageTitle eyebrow="Pemesanan">Informasi pengiriman</PageTitle>
            <Notice error>{error}</Notice>
            <form onSubmit={submit} className="space-y-5">
                <Field id="customer_name" label="Nama penerima" value={fields.customer_name} onChange={setFields} />
                <Field id="customer_email" label="Email" type="email" value={fields.customer_email} onChange={setFields} />
                <Field id="customer_phone" label="Nomor telepon" type="tel" value={fields.customer_phone} onChange={setFields} />
                <div><label htmlFor="shipping_address" className="block text-sm font-bold">Alamat lengkap</label><textarea id="shipping_address" required maxLength={2000} rows="4" value={fields.shipping_address} onChange={(event) => setFields((current) => ({ ...current, shipping_address: event.target.value }))} placeholder="Nama jalan, nomor rumah, RT/RW, patokan" className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 outline-none focus:border-violet-700" /></div>

                {options?.shipping_enabled ? <section className="rounded-2xl bg-stone-50 p-4">
                    <label htmlFor="shipping-destination" className="block text-sm font-bold">Kecamatan atau kode pos tujuan</label>
                    <input id="shipping-destination" value={destinationQuery} onChange={(event) => {
                        setDestinationQuery(event.target.value);
                        setDestination(null);
                        setQuotes([]);
                        setSelectedQuote(null);
                    }} placeholder="Contoh: Beji, Depok" className="mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3" />
                    {destinations.length > 0 && <ul className="mt-2 max-h-48 overflow-y-auto rounded-xl border border-stone-200 bg-white">{destinations.map((item) => <li key={item.id}><button type="button" onClick={() => {
                        setDestination(item);
                        setDestinationQuery(item.label);
                        setDestinations([]);
                        setQuotes([]);
                        setSelectedQuote(null);
                    }} className="w-full px-4 py-3 text-left text-sm hover:bg-violet-50">{item.label}{item.zip_code ? ` · ${item.zip_code}` : ''}</button></li>)}</ul>}
                    <button type="button" disabled={!destination || loadingQuotes} onClick={calculateShipping} className="mt-3 rounded-full border border-violet-800 px-5 py-2 text-sm font-bold text-violet-900 disabled:opacity-50">{loadingQuotes ? 'Menghitung ongkir…' : 'Cek ongkir'}</button>
                    {quotes.length > 0 && <fieldset className="mt-4 space-y-2"><legend className="mb-2 text-sm font-bold">Pilih layanan pengiriman</legend>{quotes.map((quote) => <label key={`${quote.code}-${quote.service}`} className="flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 bg-white p-3"><input type="radio" name="shipping-quote" checked={selectedQuote?.code === quote.code && selectedQuote?.service === quote.service} onChange={() => setSelectedQuote(quote)} className="mt-1" /><span className="min-w-0 flex-1 text-sm"><strong>{quote.name} {quote.service}</strong><span className="block text-stone-500">{quote.etd ? `Estimasi ${quote.etd}` : quote.description}</span></span><strong className="text-sm">{money.format(quote.cost)}</strong></label>)}</fieldset>}
                </section> : options ? <p className="rounded-xl bg-amber-50 p-3 text-sm text-amber-900">Cek ongkir otomatis belum dikonfigurasi pada server ini. Untuk penggunaan lokal, ongkir belum ditambahkan ke total.</p> : <p className="rounded-xl bg-stone-50 p-3 text-sm text-stone-600">Memeriksa metode pembayaran dan ongkos kirim…</p>}

                <div><label htmlFor="payment_method" className="block text-sm font-bold">Metode pembayaran</label><select id="payment_method" value={fields.payment_method} onChange={(event) => setFields((current) => ({ ...current, payment_method: event.target.value }))} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3"><option value="cash">Bayar di tempat (COD)</option>{options?.midtrans_enabled && <option value="midtrans">Pembayaran online (Midtrans)</option>}</select>{fields.payment_method === 'midtrans' && <p className="mt-2 text-sm text-stone-500">Anda akan diarahkan ke halaman pembayaran aman Midtrans.</p>}</div>
                {options?.checkout_enabled === false && <p className="rounded-xl bg-red-50 p-3 text-sm text-red-800">Checkout belum aktif. Konfigurasi ongkir otomatis pada server terlebih dahulu.</p>}
                <button disabled={!options || options.checkout_enabled === false || busy || (options?.shipping_enabled && (!destination || !selectedQuote))} className="w-full rounded-full bg-violet-950 px-6 py-3.5 font-bold text-white disabled:opacity-50">{busy ? 'Membuat pesanan…' : 'Buat pesanan'}</button>
            </form>
        </section>
        <aside className="h-fit rounded-3xl border border-stone-200 bg-white p-6"><h2 className="text-lg font-black">Ringkasan</h2><div className="mt-4 space-y-3">{items.map((item) => <div key={item.id} className="flex justify-between gap-3 text-sm"><span>{item.title} × {item.quantity}</span><span>{money.format(item.price * item.quantity)}</span></div>)}</div><div className="mt-4 flex justify-between border-t border-stone-100 pt-4 text-sm"><span>Subtotal</span><span>{money.format(total)}</span></div><div className="mt-3 flex justify-between text-sm"><span>Ongkos kirim</span><span>{options?.shipping_enabled && !selectedQuote ? 'Pilih kurir' : money.format(shippingCost)}</span></div><div className="mt-4 flex justify-between border-t border-stone-200 pt-4 font-black"><span>Total</span><span>{money.format(total + shippingCost)}</span></div></aside>
    </div>;
}

function Field({ id, label, type = 'text', value, onChange }) {
    return <div><label htmlFor={id} className="block text-sm font-bold">{label}</label><input id={id} type={type} required value={value} onChange={(event) => onChange((fields) => ({ ...fields, [id]: event.target.value }))} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 outline-none focus:border-violet-700" /></div>;
}
