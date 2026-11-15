import { useState } from 'react';
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom';
import PasswordInput from '../components/PasswordInput';
import { Notice, PageTitle } from '../components/Notice';
import { useAuth } from '../contexts/AuthContext';
import { errorMessage } from '../services/api';

export default function LoginPage() {
    const { user, isAdmin, login } = useAuth();
    const [fields, setFields] = useState({ email: '', password: '' });
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(false);
    const navigate = useNavigate();
    const location = useLocation();

    if (user) return <Navigate to={isAdmin ? '/admin' : '/'} replace />;

    async function submit(event) {
        event.preventDefault();
        setBusy(true);
        setError('');
        try {
            const account = await login(fields);
            navigate(account.is_admin ? '/admin' : (location.state?.from ?? '/'), { replace: true });
        } catch (reason) {
            setError(errorMessage(reason));
        } finally {
            setBusy(false);
        }
    }

    return <section className="mx-auto max-w-xl rounded-[2rem] border border-stone-200 bg-white p-7 shadow-xl sm:p-10">
        <PageTitle eyebrow="Akun Ruang Baca">Masuk</PageTitle>
        <Notice error>{error}</Notice>
        <form onSubmit={submit} className="space-y-5">
            <div><label htmlFor="email" className="block text-sm font-bold text-stone-800">Email</label><input id="email" type="email" required autoComplete="username" value={fields.email} onChange={(event) => setFields({ ...fields, email: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 outline-none focus:border-violet-700" /></div>
            <PasswordInput id="password" label="Kata sandi" value={fields.password} onChange={(event) => setFields({ ...fields, password: event.target.value })} />
            <button disabled={busy} className="w-full rounded-full bg-violet-950 px-6 py-3.5 font-bold text-white hover:bg-violet-800 disabled:opacity-60">{busy ? 'Memproses…' : 'Masuk'}</button>
        </form>
        <p className="mt-6 text-center text-sm text-stone-600">Belum punya akun? <Link to="/register" className="font-bold text-violet-900">Daftar sekarang</Link></p>
    </section>;
}
