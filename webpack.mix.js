let mix = require('laravel-mix');

mix.js('admin/js/juvo-mail-editor.js', 'js')
	.sass('admin/scss/juvo-mail-editor.scss', 'css')
	.setPublicPath('admin/dist')
	.setResourceRoot('./');
