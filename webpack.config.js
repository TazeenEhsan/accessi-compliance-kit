const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		admin: path.resolve( process.cwd(), 'assets/js/src/admin', 'index.js' ),
		guide: path.resolve( process.cwd(), 'assets/js/src/guide', 'index.js' ),
		scanner: path.resolve( process.cwd(), 'assets/js/src/scanner', 'index.js' ),
		fixes: path.resolve( process.cwd(), 'assets/js/src/fixes', 'index.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'build' ),
	},
	optimization: {
		...defaultConfig.optimization,
		// The `scanner` bundle includes axe-core (MPL-2.0), whose license requires
		// its notice to survive distribution. @wordpress/scripts' default Terser
		// config strips all comments, so this extracts `/*! ... */`-style banners
		// (axe-core's included) to a `<file>.LICENSE.txt` sidecar instead of
		// dropping them.
		minimizer: [
			new TerserPlugin( {
				parallel: true,
				terserOptions: {
					output: {
						comments: /translators:/i,
					},
					compress: {
						passes: 2,
					},
					mangle: {
						reserved: [ '__', '_n', '_nx', '_x' ],
					},
				},
				extractComments: {
					condition: /^\**!|@preserve|@license|@cc_on/i,
					filename: ( fileData ) => `${ fileData.filename }.LICENSE.txt`,
				},
			} ),
		],
	},
};
