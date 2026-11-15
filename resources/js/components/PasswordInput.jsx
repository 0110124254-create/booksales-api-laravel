import { useState } from 'react';

export default function PasswordInput({ id, label, value, onChange, required = true, autoComplete = 'current-password' }) {
    const [visible, setVisible] = useState(false);

    return (
        <div>
            <label htmlFor={id} className="block text-sm font-bold text-stone-800">{label}</label>
            <div className="relative mt-2">
                <input
                    id={id}
                    name={id}
                    type={visible ? 'text' : 'password'}
                    value={value}
                    onChange={onChange}
                    required={required}
                    autoComplete={autoComplete}
                    className="w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3.5 pr-28 text-stone-950 outline-none focus:border-violet-700 focus:bg-white focus:ring-4 focus:ring-violet-700/10"
                />
                <button
                    type="button"
                    onClick={() => setVisible((current) => !current)}
                    aria-controls={id}
                    aria-pressed={visible}
                    className="absolute inset-y-0 right-3 my-auto rounded-lg px-2 text-sm font-bold text-violet-900 hover:bg-violet-50"
                >
                    {visible ? 'Sembunyikan' : 'Tampilkan'}
                </button>
            </div>
        </div>
    );
}
