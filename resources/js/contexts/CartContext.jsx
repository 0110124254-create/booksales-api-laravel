import { createContext, useContext, useMemo, useState } from 'react';

const CartContext = createContext(null);
const storageKey = 'ruangbaca_cart';

function readCart() {
    try {
        return JSON.parse(window.localStorage.getItem(storageKey) ?? '[]');
    } catch {
        return [];
    }
}

export function CartProvider({ children }) {
    const [items, setItems] = useState(readCart);

    function update(next) {
        setItems(next);
        window.localStorage.setItem(storageKey, JSON.stringify(next));
    }

    const value = useMemo(() => ({
        items,
        count: items.reduce((total, item) => total + item.quantity, 0),
        total: items.reduce((total, item) => total + item.price * item.quantity, 0),
        add(book) {
            if (book.stock < 1) return;
            const existing = items.find((item) => item.id === book.id);
            const next = existing
                ? items.map((item) => item.id === book.id ? { ...item, quantity: Math.min(item.quantity + 1, book.stock) } : item)
                : [...items, { id: book.id, title: book.title, price: book.price, stock: book.stock, cover: book.cover, quantity: 1 }];
            update(next);
        },
        setQuantity(id, quantity) {
            const next = items
                .map((item) => item.id === id ? { ...item, quantity: Math.min(Math.max(1, Number(quantity) || 1), item.stock) } : item);
            update(next);
        },
        remove(id) { update(items.filter((item) => item.id !== id)); },
        clear() { update([]); },
    }), [items]);

    return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
    const context = useContext(CartContext);
    if (!context) throw new Error('useCart must be used inside CartProvider');
    return context;
}
