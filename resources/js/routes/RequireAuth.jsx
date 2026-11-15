import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function RequireAuth({ admin = false, customer = false }) {
    const { user, loading, isAdmin } = useAuth();

    if (loading) return <p className="mx-auto max-w-7xl px-5 py-16 text-stone-600">Memuat akun…</p>;
    if (!user) return <Navigate to="/login" replace />;
    if (admin && !isAdmin) return <Navigate to="/" replace />;
    if (customer && isAdmin) return <Navigate to="/admin" replace />;

    return <Outlet />;
}
