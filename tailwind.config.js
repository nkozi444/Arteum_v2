// tailwind.config.js
module.exports = {
  content: [
    // Include all your Twig templates
    "./templates/**/*.html.twig", 
    // If you use any JS/TS files for front-end components
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}