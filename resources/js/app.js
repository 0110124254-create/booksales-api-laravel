import React from 'react';
import { createRoot } from 'react-dom/client';

function PasswordVisibility({ inputId }) {
    const [visible, setVisible] = React.useState(false);
    const input = document.getElementById(inputId);

    React.useEffect(() => {
        if (input) input.type = visible ? 'text' : 'password';
    }, [input, visible]);

    return React.createElement(
        'button',
        {
            type: 'button',
            className: 'absolute inset-y-0 right-3 my-auto rounded-lg px-2 text-sm font-bold text-violet-900 hover:bg-violet-50 focus:outline-none focus:ring-2 focus:ring-violet-700',
            'aria-controls': inputId,
            'aria-pressed': visible,
            'aria-label': `${visible ? 'Sembunyikan' : 'Tampilkan'} kata sandi`,
            onClick: () => setVisible((current) => !current),
        },
        visible ? 'Sembunyikan' : 'Tampilkan',
    );
}

document.querySelectorAll('[data-password-toggle-root]').forEach((element) => {
    createRoot(element).render(React.createElement(PasswordVisibility, { inputId: element.dataset.passwordToggleRoot }));
});
