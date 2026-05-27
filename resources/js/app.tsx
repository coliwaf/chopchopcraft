import './bootstrap';
import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route, Config } from 'ziggy-js'
// @ts-ignore
window.route = route


// createInertiaApp({
// 	resolve: (name) => {
// 		const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
// 		return pages[`./Pages/${name}.tsx`];
// 	},
// 	setup({ el, App, props }) {
// 		createRoot(el).render(<App {...props} />);
// 	},
// 	progress: { color: '#4B5563' },
// });


// Make route() available globally so components can import it directly
// from 'ziggy-js' without needing window.route everywhere.
// declare global {
//     function route(name: string, params?: unknown, absolute?: boolean, config?: Config): string
// }

// createInertiaApp({
// 	resolve: (name) => {
// 		const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
// 		return pages[`./Pages/${name}.tsx`];
// 	},
// 	setup({ el, App, props }) {
// 		createRoot(el).render(<App {...props} />);
// 	},
// 	progress: { color: '#4B5563', showSpinner: false },
// });

// createInertiaApp({
//     title: (title) => title ? `${title} – Chopping Board Shop` : 'Chopping Board Shop',
//     resolve: (name) =>
//         resolvePageComponent(
//             `./Pages/${name}.tsx`,
//             import.meta.glob('./Pages/**/*.tsx'),
//         ),
//     setup({ el, App, props }) {
//         createRoot(el).render(<App {...props} />)
//     },
//     progress: { color: '#78350f', showSpinner: false },
// })

declare global {
    function route(name: string, params?: unknown, absolute?: boolean, config?: Config): string
}

// Eagerly import ALL page components at build time.
// import.meta.glob MUST be an inline string literal — Vite evaluates it
// statically at build time and cannot accept a dynamic/variable pattern.
// Using { eager: true } avoids the async resolvePageComponent helper which
// causes "Page not found" errors in some Vite + Inertia setups.
const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true })

createInertiaApp({
    title: (title) => title ? `${title} – Chop Chop Craft` : 'Chop Chop Craft ',

    resolve: (name) => {
        // Inertia sends e.g. "Home" or "Products/Index".
        // We build the key that matches what import.meta.glob produces.
        const key = `./Pages/${name}.tsx`
        const page = pages[key]

        if (!page) {
            throw new Error(
                `Page component not found: "${key}"\n` +
                `Available pages:\n${Object.keys(pages).join('\n')}`
            )
        }

        // Eager glob returns the module directly (no .default needed for named exports,
        // but default exports — which all our pages use — are on .default)
        return (page as any).default
    },

    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />)
    },

    progress: { color: '#78350f', showSpinner: false },
})
