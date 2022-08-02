const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [...defaultTheme.fontFamily.mono],
            },
            colors: {
                soketi1: {
                    50: '#f9f6ff',
                    100: '#f4eeff',
                    200: '#e2d4ff',
                    300: '#d1baff',
                    400: '#af86ff',
                    500: '#8c52ff',
                    600: '#7e4ae6',
                    700: '#693ebf',
                    800: '#543199',
                    900: '#45287d',
                },
                soketi2: {
                    50: '#f7f3fe',
                    100: '#efe8fd',
                    200: '#d7c5fa',
                    300: '#bfa2f7',
                    400: '#8e5df1',
                    500: '#5e17eb',
                    600: '#5515d4',
                    700: '#4711b0',
                    800: '#380e8d',
                    900: '#2e0b73',
                },
            },
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
