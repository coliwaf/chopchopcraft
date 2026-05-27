import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';
import svgr from 'vite-plugin-svgr';

export default defineConfig({
	plugins: [
		laravel({
			input: 'resources/js/app.tsx',
			refresh: true,
		}),
		react(),
		svgr(),
	],
	resolve: {
		alias: {
			// Allows imports like: import Foo from '@/Components/Foo'
			'@': path.resolve(__dirname, 'resources/js'),
		},
	},
	server: {
		// REQUIRED for Docker/Sail — Vite must bind to all interfaces
		host: '0.0.0.0',
		port: 5173,
		hmr: {
			// Tell the browser to connect HMR back to localhost (your machine)
			host: 'localhost',
		},
	},
});
