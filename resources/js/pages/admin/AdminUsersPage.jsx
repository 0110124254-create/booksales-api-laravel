import { useEffect, useState } from 'react';
import AdminNav from '../../components/AdminNav';
import { Notice, PageTitle } from '../../components/Notice';
import api, { errorMessage } from '../../services/api';

export default function AdminUsersPage() {
    const [users, setUsers] = useState([]);
    const [error, setError] = useState('');
    useEffect(() => { api.get('/admin/users').then((response) => setUsers(response.data.data.data ?? [])).catch((reason) => setError(errorMessage(reason))); }, []);
    return <><AdminNav /><PageTitle eyebrow="Manajemen">Pengguna terdaftar</PageTitle><Notice error>{error}</Notice>
        <div className="overflow-x-auto rounded-3xl border border-stone-200 bg-white"><table className="w-full min-w-[620px] text-left text-sm"><thead className="bg-stone-50"><tr><th className="p-4">Nama</th><th className="p-4">Username</th><th className="p-4">Email</th><th className="p-4">Pesanan</th><th className="p-4">Terdaftar</th></tr></thead><tbody className="divide-y divide-stone-100">{users.map((user) => <tr key={user.id}><td className="p-4 font-bold">{user.name}</td><td className="p-4">{user.username ?? '—'}</td><td className="p-4">{user.email}</td><td className="p-4">{user.sales_count}</td><td className="p-4">{new Date(user.created_at).toLocaleDateString('id-ID')}</td></tr>)}</tbody></table></div>
    </>;
}
