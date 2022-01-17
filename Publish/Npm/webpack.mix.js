const mix = require('laravel-mix');

mix.webpackConfig({
	module: {
		rules: [
			// Add support for Vue ref sugar (let ref = $ref())
			{
				test: /\.vue$/,
				loader: "vue-loader",
				options: {
					reactivityTransform: true,
				},
			},
			// Add support for <style lang="postcss"> blocks
			{
				test: /\.(postcss)$/,
				use: ["vue-style-loader", { loader: "css-loader", options: { importLoaders: 1 } }, "postcss-loader"],
			},
		],
	},
});

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
// Normal js files
mix.js('resources/vendor/Magnifier/js/app.js', 'public/vendor/Magnifier/js')
    .sourceMaps()
    .version();

// Vue js example
mix.js('resources/vendor/Magnifier/js/vue.js', 'public/vendor/Magnifier/js')
    .vue({version: 3})
    .sourceMaps()
    .version();

const tailwindcss = require('tailwindcss')

mix.sass('resources/vendor/Magnifier/sass/app.scss', 'public/vendor/Magnifier/css')
   .options({
      processCssUrls: false,
      postCss: [ tailwindcss('tailwind.config.js') ],
});
