const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            '@' : __dirname + '/resources/js',
            '_c': __dirname + '/resources/js/components',
            '_v': __dirname + '/resources/js/view',
        },
    },
})

mix.js('resources/js/app.js', 'public/js/app.min.js')
    // .sass('resources/sass/app.scss', 'public/css');
