import { useEffect, useState } from 'react';
import AdminNav from './AdminNav';
import { Notice, PageTitle } from './Notice';
import api, { errorMessage } from '../services/api';

const definitions = {
    genres: { title: 'Genre', endpoint: '/genres', fields: [['name', 'Nama genre'], ['description', 'Deskripsi', 'textarea']], columns: [['name', 'Nama'], ['description', 'Deskripsi'], ['books_count', 'Jumlah buku']] },
    authors: { title: 'Penulis', endpoint: '/authors', fields: [['name', 'Nama penulis'], ['nationality', 'Kebangsaan'], ['description', 'Deskripsi', 'textarea']], columns: [['name', 'Nama'], ['nationality', 'Kebangsaan'], ['books_count', 'Jumlah buku']] },
};

export default function AdminSimpleResourcePage({ resource }) {
    const definition = definitions[resource];
    const [rows, setRows] = useState([]);
    const [editing, setEditing] = useState(null);
    const [fields, setFields] = useState({});
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');

    async function reload() {
        const response = await api.get(definition.endpoint);
        setRows(response.data.data?.data ?? response.data.data ?? []);
    }
    useEffect(() => { reload().catch((reason) => setError(errorMessage(reason))); }, [definition.endpoint]);

    function edit(row = null) {
        setEditing(row);
        setFields(row ? Object.fromEntries(definition.fields.map(([key]) => [key, row[key] ?? ''])) : {});
    }

    async function submit(event) {
        event.preventDefault();
        setError('');
        try {
            if (editing) await api.put(`${definition.endpoint}/${editing.id}`, fields);
            else await api.post(definition.endpoint, fields);
            edit();
            setNotice(`${definition.title} berhasil disimpan.`);
            await reload();
        } catch (reason) { setError(errorMessage(reason)); }
    }

    async function remove(row) {
        if (!window.confirm(`Hapus ${definition.title.toLowerCase()} “${row.name}”?`)) return;
        try { await api.delete(`${definition.endpoint}/${row.id}`); setNotice(`${definition.title} dihapus.`); await reload(); }
        catch (reason) { setError(errorMessage(reason)); }
    }

    return <><AdminNav /><PageTitle eyebrow="Kelola katalog">{definition.title}</PageTitle><Notice>{notice}</Notice><Notice error>{error}</Notice>
        <form onSubmit={submit} className="mb-7 grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 sm:grid-cols-2">
            <h2 className="text-lg font-black sm:col-span-2">{editing ? `Edit ${definition.title.toLowerCase()}` : `Tambah ${definition.title.toLowerCase()}`}</h2>
            {definition.fields.map(([key, label, type]) => <div key={key} className={type === 'textarea' ? 'sm:col-span-2' : ''}><label htmlFor={key} className="block text-sm font-bold">{label}</label>{type === 'textarea' ? <textarea id={key} rows="3" value={fields[key] ?? ''} onChange={(event) => setFields({ ...fields, [key]: event.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /> : <input id={key} required={key === 'name'} value={fields[key] ?? ''} onChange={(event) => setFields({ ...fields, [key]: event.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" />}</div>)}
            <div className="flex gap-3 sm:col-span-2"><button className="rounded-full bg-violet-950 px-6 py-3 font-bold text-white">Simpan</button>{editing && <button type="button" onClick={() => edit()} className="rounded-full border border-stone-300 px-6 py-3 font-bold">Batal</button>}</div>
        </form>
        <div className="overflow-x-auto rounded-3xl border border-stone-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-stone-50"><tr>{definition.columns.map(([key, label]) => <th key={key} className="p-4">{label}</th>)}<th className="p-4">Aksi</th></tr></thead><tbody className="divide-y divide-stone-100">{rows.map((row) => <tr key={row.id}>{definition.columns.map(([key]) => <td key={key} className="max-w-lg p-4">{row[key] ?? '—'}</td>)}<td className="whitespace-nowrap p-4"><button onClick={() => edit(row)} className="mr-3 font-bold text-violet-900">Edit</button><button onClick={() => remove(row)} className="font-bold text-red-700">Hapus</button></td></tr>)}</tbody></table></div>
    </>;
}
