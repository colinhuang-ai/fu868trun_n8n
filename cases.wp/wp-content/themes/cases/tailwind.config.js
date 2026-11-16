/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: ['./*.php', './inc/**/*.php', './template-parts/**/*.php', './assets/js/main.js'],
  theme: {
    extend: {
      colors: {
        primary: '#0056b3',
        'primary-dark': '#003d82',
        secondary: '#f0f8ff',
        'background-light': '#ffffff',
        'background-dark': '#111827',
        'surface-light': '#f9fafb',
        'surface-dark': '#1f2937',
        'text-light': '#374151',
        'text-dark': '#d1d5db',
        'heading-light': '#111827',
        'heading-dark': '#f3f4f6',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        body: ['Inter', 'sans-serif'],
        display: ['Playfair Display', 'serif'],
        headline: ['Plus Jakarta Sans', 'sans-serif'],
        'plus-jakarta-sans': ['Plus Jakarta Sans', 'sans-serif'],
      },
      borderRadius: { DEFAULT: '0.5rem' },
    },
  },
  plugins: [require('@tailwindcss/forms')],
};
