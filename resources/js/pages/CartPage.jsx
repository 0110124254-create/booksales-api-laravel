import { Link } from 'react-router-dom';
import { PageTitle } from '../components/Notice';
import { useCart } from '../contexts/CartContext';

const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export default function CartPage() {
    const { items, total, setQuantity, remove } = useCart();
    if (!items.length) return <><PageTitle eyebrow="Belanja">Keranjang</PageTitle><p className="rounded-3xl border border-dashed border-stone-300 p-10 text-center text-stone-600">Keranjang Anda masih kosong.</p><Link to="/" className="mt-5 inline-flex rounded-full bg-violet-950 px-5 py-3 font-bold text-white">Jelajahi katalog</Link></>;

    return <>
        <PageTitle eyebrow="Belanja">Keranjang Anda</PageTitle>
        <div className="space-y-4">{items.map((item) => <article key={item.id} className="flex flex-wrap items-center gap-4 rounded-3xl border border-stone-200 bg-white p-5">
            <div className="flex size-20 items-center justify-center overflow-hidden rounded-2xl bg-violet-50">{item.cover ? <img src={`/${item.cover}`} alt="" className="h-full w-full object-contain" /> : <span className="px-2 text-center text-xs font-bold text-violet-900">{item.title}</span>}</div>
            <div className="min-w-48 flex-1"><h2 className="font-black">{item.title}</h2><p className="mt-1 text-sm text-stone-500">{money.format(item.price)} · stok {item.stock}</p></div>
            <label className="text-sm font-bold">Jumlah <input type="number" min="1" max={item.stock} value={item.quantity} onChange={(event) => setQuantity(item.id, event.target.value)} className="ml-2 w-20 rounded-xl border border-stone-300 px-3 py-2" /></label>
            <strong className="min-w-28 text-right">{money.format(item.price * item.quantity)}</strong>
            <button onClick={() => remove(item.id)} className="rounded-full px-3 py-2 text-sm font-bold text-red-700 hover:bg-red-50">Hapus</button>
        </article>)}</div>
        <div className="ml-auto mt-8 max-w-md rounded-3xl border border-stone-200 bg-white p-6">
            <div className="flex justify-between text-lg font-black"><span>Total</span><span>{money.format(total)}</span></div>
            <Link to="/checkout" className="mt-5 block rounded-full bg-violet-950 px-6 py-3 text-center font-bold text-white hover:bg-violet-800">Lanjut checkout</Link>
        </div>
    </>;
}
