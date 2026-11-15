import { useCallback, useEffect, useState } from 'react';
import AdminNav from '../../components/AdminNav';
import { Notice, PageTitle } from '../../components/Notice';
import api, { errorMessage } from '../../services/api';

const empty = { title: '', author_id: '', genre_id: '', isbn: '', description: '', price: '', stock: '', weight_gram: '300', is_active: true, cover_upload: null };

export default function AdminBooksPage() {
    const [books, setBooks] = useState([]);
    const [authors, setAuthors] = useState([]);
    const [genres, setGenres] = useState([]);
    const [editing, setEditing] = useState(null);
    const [fields, setFields] = useState(empty);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');

    const reload = useCallback(async () => {
        const [bookResponse, authorResponse, genreResponse] = await Promise.all([api.get('/admin/books', { params: { per_page: 50 } }), api.get('/authors'), api.get('/genres')]);
        setBooks(bookResponse.data.data ?? []);
        setAuthors(authorResponse.data.data ?? []);
        setGenres(genreResponse.data.data ?? []);
    }, []);
    useEffect(() => { reload().catch((reason) => setError(errorMessage(reason))); }, [reload]);

    function startEdit(book = null) {
        setEditing(book);
        setFields(book ? {
            title: book.title,
            author_id: String(book.author_id ?? ''),
            genre_id: String(book.genre_id ?? ''),
            isbn: book.isbn ?? '',
            description: book.description ?? '',
            price: String(book.price),
            stock: String(book.stock),
            weight_gram: String(book.weight_gram ?? 300),
            is_active: Boolean(book.is_active),
            cover_upload: null,
        } : empty);
        setError('');
        setNotice('');
    }

    async function submit(event) {
        event.preventDefault();
        setError('');
        const body = new FormData();
        Object.entries(fields).forEach(([key, value]) => {
            if (key === 'cover_upload') {
                if (value) body.append(key, value);
            } else body.append(key, key === 'is_active' ? (value ? '1' : '0') : String(value));
        });
        try {
            if (editing) {
                body.append('_method', 'PATCH');
                await api.post(`/admin/books/${editing.id}`, body);
            } else await api.post('/admin/books', body);
            const success = editing ? 'Buku berhasil diperbarui.' : 'Buku berhasil ditambahkan.';
            startEdit();
            setNotice(success);
            await reload();
        } catch (reason) { setError(errorMessage(reason)); }
    }

    async function remove(book) {
        if (!window.confirm(`Hapus buku “${book.title}”?`)) return;
        try { await api.delete(`/admin/books/${book.id}`); setNotice('Buku dihapus.'); await reload(); }
        catch (reason) { setError(errorMessage(reason)); }
    }

    return <><AdminNav /><PageTitle eyebrow="Kelola katalog">Buku</PageTitle><Notice>{notice}</Notice><Notice error>{error}</Notice>
        <section className="mb-8 rounded-3xl border border-stone-200 bg-white p-6 sm:p-8">
            <h2 className="mb-5 text-lg font-black">{editing ? 'Edit buku' : 'Tambah buku'}</h2>
            <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                <Input id="title" label="Judul buku" value={fields.title} onChange={setFields} />
                <Select id="author_id" label="Penulis" value={fields.author_id} options={authors.map((item) => [item.id, item.name])} onChange={setFields} />
                <Select id="genre_id" label="Genre" value={fields.genre_id} options={genres.map((item) => [item.id, item.name])} onChange={setFields} />
                <Input id="isbn" label="ISBN" value={fields.isbn} onChange={setFields} required={false} />
                <Input id="price" label="Harga (rupiah)" type="number" min="0" value={fields.price} onChange={setFields} />
                <Input id="stock" label="Stok" type="number" min="0" value={fields.stock} onChange={setFields} />
                <Input id="weight_gram" label="Berat buku (gram)" type="number" min="1" value={fields.weight_gram} onChange={setFields} />
                <div><label htmlFor="cover_upload" className="block text-sm font-bold">Sampul buku</label><input id="cover_upload" type="file" accept="image/jpeg,image/png,image/webp" onChange={(event) => setFields({ ...fields, cover_upload: event.target.files?.[0] ?? null })} className="mt-2 w-full rounded-xl border border-stone-300 p-2 text-sm" /></div>
                <label className="flex items-center gap-3 self-end pb-3 text-sm font-bold"><input type="checkbox" checked={fields.is_active} onChange={(event) => setFields({ ...fields, is_active: event.target.checked })} /> Tampilkan di katalog</label>
                <div className="sm:col-span-2"><label htmlFor="description" className="block text-sm font-bold">Deskripsi</label><textarea id="description" rows="3" value={fields.description} onChange={(event) => setFields({ ...fields, description: event.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></div>
                <div className="flex gap-3 sm:col-span-2"><button className="rounded-full bg-violet-950 px-6 py-3 font-bold text-white">Simpan</button>{editing && <button type="button" onClick={() => startEdit()} className="rounded-full border border-stone-300 px-6 py-3 font-bold">Batal</button>}</div>
            </form>
        </section>
        <div className="overflow-x-auto rounded-3xl border border-stone-200 bg-white"><table className="w-full min-w-[720px] text-left text-sm"><thead className="bg-stone-50 text-stone-600"><tr><th className="p-4">Buku</th><th className="p-4">Penulis</th><th className="p-4">Genre</th><th className="p-4">Stok</th><th className="p-4">Aksi</th></tr></thead><tbody className="divide-y divide-stone-100">{books.map((book) => <tr key={book.id}><td className="p-4 font-bold">{book.title}<span className="ml-2 text-xs text-stone-400">{book.is_active ? '' : 'Nonaktif'}</span></td><td className="p-4">{book.author_record?.name ?? '—'}</td><td className="p-4">{book.genre_record?.name ?? '—'}</td><td className="p-4">{book.stock}</td><td className="p-4"><button onClick={() => startEdit(book)} className="mr-3 font-bold text-violet-900">Edit</button><button onClick={() => remove(book)} className="font-bold text-red-700">Hapus</button></td></tr>)}</tbody></table></div>
    </>;
}

function Input({ id, label, value, onChange, type = 'text', required = true, min }) {
    return <div><label htmlFor={id} className="block text-sm font-bold">{label}</label><input id={id} type={type} min={min} required={required} value={value} onChange={(event) => onChange((current) => ({ ...current, [id]: event.target.value }))} className="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3" /></div>;
}

function Select({ id, label, value, options, onChange }) {
    return <div><label htmlFor={id} className="block text-sm font-bold">{label}</label><select id={id} required value={value} onChange={(event) => onChange((current) => ({ ...current, [id]: event.target.value }))} className="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3"><option value="">Pilih {label.toLowerCase()}</option>{options.map(([optionValue, optionLabel]) => <option value={optionValue} key={optionValue}>{optionLabel}</option>)}</select></div>;
}
