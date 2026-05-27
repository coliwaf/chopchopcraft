/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
        './resources/js/**/*.ts',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Hanken Grotesk', 'system-ui', 'sans-serif'],
                display: ['Hanken Grotesk', 'system-ui', 'sans-serif'],
                mono: ['ui-monospace', 'SFMono-Regular', 'monospace'],
            },
            colors: {
                // Warm wood-inspired palette
                wood: {
                    50: '#fdf8f0',
                    100: '#faefd8',
                    200: '#f3d9a8',
                    300: '#eabc6e',
                    400: '#e09e42',
                    500: '#d4852a',
                    600: '#b86a21',
                    700: '#96511e',
                    800: '#7a421f',
                    900: '#64371c',
                },
            },
            borderRadius: {
                xl: '0.875rem',
                '2xl': '1.25rem',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
