/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "src/**/*.js",
    "src/**/*.vue",
   
  ],
  theme: {
    extend: {},
  },
  plugins: [require('@tailwindcss/typography'),require('@tailwindcss/forms')],
}

