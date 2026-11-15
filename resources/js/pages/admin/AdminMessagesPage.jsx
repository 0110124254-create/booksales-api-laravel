import { useEffect, useState } from 'react';
import AdminNav from '../../components/AdminNav';
import { Notice, PageTitle } from '../../components/Notice';
import api, { errorMessage } from '../../services/api';

export default function AdminMessagesPage() {
    const [messages, setMessages] = useState([]);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    async function reload() { const response = await api.get('/admin/messages'); setMessages(response.data.data.data ?? []); }
    useEffect(() => { reload().catch((reason) => setError(errorMessage(reason))); }, []);
    async function markRead(message) {
        try { await api.patch(`/admin/messages/${message.id}`); setNotice('Pesan ditandai sudah dibaca.'); await reload(); }
        catch (reason) { setError(errorMessage(reason)); }
    }
    return <><AdminNav /><PageTitle eyebrow="Kotak masuk">Pesan dari pengguna</PageTitle><Notice>{notice}</Notice><Notice error>{error}</Notice>
        <div className="space-y-4">{messages.map((message) => <article key={message.id} className="rounded-3xl border border-stone-200 bg-white p-6"><div className="flex flex-wrap justify-between gap-3"><div><h2 className="font-black">{message.subject}</h2><p className="mt-1 text-sm text-stone-500">{message.name} · {message.email}</p></div><div className="flex items-center gap-3">{message.read_at ? <span className="text-sm font-bold text-emerald-700">Sudah dibaca</span> : <button onClick={() => markRead(message)} className="rounded-full bg-violet-950 px-4 py-2 text-sm font-bold text-white">Tandai dibaca</button>}</div></div><p className="mt-4 whitespace-pre-line leading-7 text-stone-700">{message.message}</p></article>)}</div>
    </>;
}
