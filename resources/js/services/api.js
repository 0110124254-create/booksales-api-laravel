import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: { Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
    const token = window.localStorage.getItem('ruangbaca_token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

export function responseData(response) {
    return response.data?.data;
}

export function errorMessage(error) {
    const errors = error.response?.data?.errors;
    if (errors) return Object.values(errors).flat().join(' ');
    return error.response?.data?.message ?? 'Terjadi kesalahan. Coba lagi.';
}

export default api;
