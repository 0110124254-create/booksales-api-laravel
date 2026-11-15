import { useState } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import PasswordInput from '../components/PasswordInput';
import { Notice, PageTitle } from '../components/Notice';
import { useAuth } from '../contexts/AuthContext';
import { errorMessage } from '../services/api';

export default function RegisterPage() {
    const { user, register } = useAuth();
    const [fields, setFields] = useState({ name: '', username: '', email: '', password: '', password_confirmation: '' });
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(false);
    const navigate = useNavigate();
    if (user) return <Navigate to="/" replace />;

    async function submit(event) {
        event.preventDefault();
        if (fields.password !== fields.password_confirmation) {
            setError('Konfirmasi kata sandi tidak cocok.');
            return;
        }
        setBusy(true);
        setError('');
        try {
            await register(fields);
            navigate('/');
        } catch (reason) {
            setError(errorMessage(reason));
        } finally {
            setBusy(false);
        }
    }

    return <section className="mx-auto max-w-2xl rounded-[2rem] border border-stone-200 bg-white p-7 shadow-xl sm:p-10">
        <PageTitle eyebrow="Akun Ruang Baca">Daftar sebagai user</PageTitle>
        <Notice error>{error}</Notice>
        <form onSubmit={submit} className="grid gap-5 sm:grid-cols-2">
            <Field id="name" label="Nama lengkap" value={fields.name} onChange={setFields} />
            <Field id="username" label="Username" value={fields.username} onChange={setFields} />
            <div className="sm:col-span-2"><Field id="email" label="Email" type="email" value={fields.email} onChange={setFields} /></div>
            <PasswordInput id="password" label="Kata sandi" autoComplete="new-password" value={fields.password} onChange={(event) => setFields({ ...fields, password: event.target.value })} />
            <PasswordInput id="password_confirmation" label="Ulangi kata sandi" autoComplete="new-password" value={fields.password_confirmation} onChange={(event) => setFields({ ...fields, password_confirmation: event.target.value })} />
            <button disabled={busy} className="rounded-full bg-violet-950 px-6 py-3.5 font-bold text-white hover:bg-violet-800 disabled:opacity-60 sm:col-span-2">{busy ? 'Memproses…' : 'Buat akun'}</button>
        </form>
        <p className="mt-6 text-center text-sm text-stone-600">Sudah punya akun? <Link to="/login" className="font-bold text-violet-900">Masuk</Link></p>
    </section>;
}

function Field({ id, label, type = 'text', value, onChange }) {
    return <div><label htmlFor={id} className="block text-sm font-bold text-stone-800">{label}</label><input id={id} type={type} required value={value} onChange={(event) => onChange((current) => ({ ...current, [id]: event.target.value }))} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 outline-none focus:border-violet-700" /></div>;
}
