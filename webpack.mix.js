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

mix.js('resources/js/main.js', 'public/js');

mix.less('resources/less/app.less', 'public/css')
    .less('node_modules/framework7/framework7.bundle.less', 'public/css');

if (mix.inProduction()) {

    mix.version();

    mix.webpackConfig({
        module: {
            rules: [
                {
                    test: /\.js?$/,
                    exclude: /(node_modules)/,
                    use: [{
                        loader: 'babel-loader',
                        options: mix.config.babel()
                    }]
                }
            ]
        }
    });
}
