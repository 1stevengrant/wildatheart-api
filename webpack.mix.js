let mix = require('laravel-mix');

mix.browserSync({
    proxy: 'https://wahfires.test',
    files:[
        './public/**/*.css',
        './public/**/*.js',
    ]
})
