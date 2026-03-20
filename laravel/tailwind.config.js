/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './laravel/resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f0f7ff',
          100: '#e0effe',
          200: '#bae0fd',
          300: '#7cc8fb',
          400: '#37adf7',
          500: '#0d93e8',
          600: '#0174c6',
          700: '#025ca1',
          800: '#064e85',
          900: '#0b416e',
          DEFAULT: '#0b416e',
        },
      },
    },
  },
  plugins: [],
};
