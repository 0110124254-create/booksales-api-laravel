import { Route, Routes } from 'react-router-dom';
import AppLayout from './layouts/AppLayout';
import RequireAuth from './routes/RequireAuth';
import HomePage from './pages/HomePage';
import BookDetailPage from './pages/BookDetailPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import CartPage from './pages/CartPage';
import CheckoutPage from './pages/CheckoutPage';
import OrdersPage from './pages/OrdersPage';
import OrderDetailPage from './pages/OrderDetailPage';
import AboutPage from './pages/AboutPage';
import ContactPage from './pages/ContactPage';
import AdminDashboardPage from './pages/admin/AdminDashboardPage';
import AdminBooksPage from './pages/admin/AdminBooksPage';
import AdminGenresPage from './pages/admin/AdminGenresPage';
import AdminAuthorsPage from './pages/admin/AdminAuthorsPage';
import AdminUsersPage from './pages/admin/AdminUsersPage';
import AdminOrdersPage from './pages/admin/AdminOrdersPage';
import AdminMessagesPage from './pages/admin/AdminMessagesPage';

export default function App() {
    return <Routes>
        <Route element={<AppLayout />}>
            <Route index element={<HomePage />} />
            <Route path="books/:id" element={<BookDetailPage />} />
            <Route path="about" element={<AboutPage />} />
            <Route path="login" element={<LoginPage />} />
            <Route path="register" element={<RegisterPage />} />
            <Route path="cart" element={<CartPage />} />
            <Route element={<RequireAuth customer />}>
                <Route path="checkout" element={<CheckoutPage />} />
                <Route path="account/orders" element={<OrdersPage />} />
                <Route path="orders/:reference" element={<OrderDetailPage />} />
                <Route path="contact" element={<ContactPage />} />
            </Route>
            <Route path="admin" element={<RequireAuth admin />}>
                <Route index element={<AdminDashboardPage />} />
                <Route path="books" element={<AdminBooksPage />} />
                <Route path="genres" element={<AdminGenresPage />} />
                <Route path="authors" element={<AdminAuthorsPage />} />
                <Route path="users" element={<AdminUsersPage />} />
                <Route path="orders" element={<AdminOrdersPage />} />
                <Route path="messages" element={<AdminMessagesPage />} />
            </Route>
            <Route path="*" element={<NotFound />} />
        </Route>
    </Routes>;
}

function NotFound() {
    return <section className="py-20 text-center"><h1 className="font-display text-4xl font-bold">Halaman tidak ditemukan</h1><a href="/" className="mt-5 inline-block font-bold text-violet-900">Kembali ke beranda</a></section>;
}
