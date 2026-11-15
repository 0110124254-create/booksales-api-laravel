import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import api, { responseData } from '../services/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!window.localStorage.getItem('ruangbaca_token')) {
            setLoading(false);
            return;
        }

        api.get('/user')
            .then((response) => setUser(response.data))
            .catch(() => {
                window.localStorage.removeItem('ruangbaca_token');
                setUser(null);
            })
            .finally(() => setLoading(false));
    }, []);

    const value = useMemo(() => ({
        user,
        loading,
        isAdmin: Boolean(user?.is_admin),
        async login(credentials) {
            const response = await api.post('/login', credentials);
            window.localStorage.setItem('ruangbaca_token', responseData(response).token);
            setUser(responseData(response).user);
            return responseData(response).user;
        },
        async register(details) {
            const response = await api.post('/register', details);
            window.localStorage.setItem('ruangbaca_token', responseData(response).token);
            setUser(responseData(response).user);
            return responseData(response).user;
        },
        async logout() {
            try {
                await api.post('/logout');
            } finally {
                window.localStorage.removeItem('ruangbaca_token');
                setUser(null);
            }
        },
    }), [user, loading]);

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (!context) throw new Error('useAuth must be used inside AuthProvider');
    return context;
}
