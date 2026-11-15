import { useState } from 'react';
import { Notice, PageTitle } from '../components/Notice';
import { useAuth } from '../contexts/AuthContext';
import api, { errorMessage } from '../services/api';

export default function ContactPage() {
    const { user } = useAuth();
    const [fields, setFields] = useState({ name: user?.name ?? '', email: user?.email ?? '', subject: '', message: '' });
    const [notice, setNotice] = useState('');
    const [error, setError] = useState('');

    async function submit(event) {
        event.preventDefault();
        setError('');
        setNotice('');
        try {
            await api.post('/contact', fields);
            setNotice('Pesan berhasil dikirim kepada admin.');
            setFields({ ...fields, subject: '', message: '' });
        } catch (reason) {
            setError(errorMessage(reason));
        }
    }

    return <section className="mx-auto max-w-2xl rounded-[2rem] border border-stone-200 bg-white p-7 sm:p-10">
        <PageTitle eyebrow="Bantuan">Hubungi admin</PageTitle>
        <Notice>{notice}</Notice><Notice error>{error}</Notice>
        <form onSubmit={submit} className="space-y-5">
            <Input id="name" label="Nama" value={fields.name} onChange={setFields} />
            <Input id="email" label="Email" type="email" value={fields.email} onChange={setFields} />
            <Input id="subject" label="Subjek" value={fields.subject} onChange={setFields} />
            <div><label htmlFor="message" className="block text-sm font-bold">Pesan</label><textarea id="message" required minLength="10" maxLength="5000" rows="5" value={fields.message} onChange={(event) => setFields({ ...fields, message: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3" /></div>
            <button className="rounded-full bg-violet-950 px-6 py-3 font-bold text-white">Kirim pesan</button>
        </form>
    </section>;
}

function Input({ id, label, type = 'text', value, onChange }) {
    return <div><label htmlFor={id} className="block text-sm font-bold">{label}</label><input id={id} type={type} required value={value} onChange={(event) => onChange({ ...value, [id]: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3" /></div>;
}
