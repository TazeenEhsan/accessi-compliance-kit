const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
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
};
