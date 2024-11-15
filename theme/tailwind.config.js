/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php', // Matches root level PHP files (like index.php, search.php, etc.)
    './inc/**/*.php', // Matches all PHP files in the inc directory
    './woocommerce/**/*.php', // Matches all PHP files in the WooCommerce directory
    './templates/**/*.php', // Matches all PHP files in the templates directory
    './assets/js/**/*.js', // Matches any JavaScript files in the assets/js directory (if you use JS files with Tailwind)
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};


