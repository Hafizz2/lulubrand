import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import '../css/app.css';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Admin/Pages/**/*.jsx', { eager: true });
        const page = pages[`./Admin/Pages/${name}.jsx`]?.default;
        if (!page) {
            throw new Error(`Inertia page [${name}] not found in resources/js/Admin/Pages/${name}.jsx`);
        }
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#4f46e5',
    },
});
