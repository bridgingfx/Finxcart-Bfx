const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. This file was missing from the repository,
 | which made `npm run dev` / `npm run prod` fail (package.json references
 | laravel-mix's webpack config but there was nothing to build).
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sourceMaps();
