const mix = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .js('resources/js/app.js', 'public/js')
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js/src/'),
                '@themeConfig': path.resolve(__dirname, 'resources/js/themeConfig.js'),
                '@core': path.resolve(__dirname, 'resources/js/src/@core'),
                '@scss': path.resolve(__dirname, 'resources/js/src/@core/scss'),
                '@validations': path.resolve(__dirname, 'resources/js/src/@core/utils/validations/validations.js'),
                '@validations': path.resolve(__dirname, 'resources/js/src/@core/utils/validations/validations.js'),
                '@validationsInventory': path.resolve(__dirname, 'resources/js/src/@core/utils/validations/inventory/'),
                '@validationsKasir': path.resolve(__dirname, 'resources/js/src/@core/utils/validations/kasir/'),
                '@validationsSupplier': path.resolve(__dirname, 'resources/js/src/@core/utils/validations/supplier/'),
                '@validationsBankAccount': path.resolve(__dirname, 'resources/js/src/@core/utils/validations/bankaccount/'),
                '@axios': path.resolve(__dirname, 'resources/js/src/libs/axios'),
                '@public': path.resolve(__dirname, 'public/'),
                '@components': path.resolve(__dirname, 'resources/js/src/views/components'),
                '@myvuepdfjs': path.resolve(__dirname, 'resources/js/src/views/components/my-vue-pdfjs'),
            }
        },
        module: {
            rules: [{
                    test: /\.worker\.js$/,
                    use: { loader: "worker-loader" },
                }, {
                    test: /\.s[ac]ss$/i,
                    use: [{
                        loader: 'sass-loader',
                        options: {
                            sassOptions: {
                                includePaths: ['node_modules', 'resources/js/src/assets']
                            }
                        }
                    }]
                },
                {
                    test: /(\.(png|jpe?g|gif|webp)$|^((?!font).)*\.svg$)/,
                    loaders: {
                        loader: 'file-loader',
                        options: {
                            name: 'images/[path][name].[ext]',
                            context: '../vuexy-vuejs-bootstrap-vue-template/src/assets/images'
                        }
                    }
                }
            ]
        },
        output: {
            chunkFilename: 'js/chunks/[name].js'
        }
    })
    .sass('resources/scss/app.scss', 'public/css')
    .options({
        postCss: [require('autoprefixer'), require('postcss-rtl')]
    })
mix.copy('resources/scss/loader.css', 'public/css')

if (mix.inProduction()) {
    mix.version()
}