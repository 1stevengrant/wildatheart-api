const mix = require('laravel-mix');

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.svg$/,
                use: ['@svgr/webpack']
            }
        ]
    }
});

mix.ts('resources/ts/search.tsx', 'public/js').react();
if (!mix.inProduction()) {
    mix.browserSync({
        proxy: 'https://wahfires.test',
        files: [
            './public/js/*.js',
            './public/css/*.css'
        ]
    })
}

