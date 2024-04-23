let mix = require('laravel-mix');
mix.options({
  processCssUrls: false,
})
mix.setPublicPath('../dist');
mix.js("src/app.js","app.js").vue();
mix.postCss("src/app.css", "app.css", [
    require("tailwindcss"),
  ]);
mix.sourceMaps();