/**
 * Builds the distributable WordPress.org zip into dist/.
 *
 * Runs as the second half of `npm run build` (after `wp-scripts build`), or
 * standalone via `npm run zip`. Stages a clean copy of the plugin, installs
 * production-only Composer dependencies, and archives it as
 * dist/accessibility-compliance-kit-for-woocommerce-<version>.zip with a single
 * `accessibility-compliance-kit-for-woocommerce/` root folder.
 *
 * Zip creation uses bsdtar (Windows' built-in tar.exe / macOS tar) or `zip`
 * on Linux — PowerShell 5.1 Compress-Archive and Git Bash's GNU tar both
 * produce archives WordPress can't extract correctly.
 */

'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const { spawnSync } = require( 'child_process' );

const ROOT = path.resolve( __dirname, '..' );
const SLUG = 'accessibility-compliance-kit-for-woocommerce';
const VERSION = require( path.join( ROOT, 'package.json' ) ).version;

const DIST_DIR = path.join( ROOT, 'dist' );
const STAGE_ROOT = path.join( DIST_DIR, '.staging' );
const STAGE_DIR = path.join( STAGE_ROOT, SLUG );
const ZIP_PATH = path.join( DIST_DIR, `${ SLUG }-${ VERSION }.zip` );

// Everything that ships in the release zip. composer.lock is staged only so
// `composer install` is deterministic; it is removed again before archiving.
const INCLUDE = [
	'accessibility-compliance-kit-for-woocommerce.php',
	'readme.txt',
	'composer.json',
	'composer.lock',
	'src',
	'build',
	'assets/css',
	'assets/images',
	'languages',
];

function fail( message ) {
	console.error( `\n[build-zip] ERROR: ${ message }` );
	process.exit( 1 );
}

function run( command, args, options = {} ) {
	const result = spawnSync( command, args, { stdio: 'inherit', ...options } );
	if ( result.error ) {
		fail( `${ command } could not be started: ${ result.error.message }` );
	}
	if ( result.status !== 0 ) {
		fail( `${ command } ${ args.join( ' ' ) } exited with code ${ result.status }` );
	}
}

/**
 * Resolve a runnable Composer invocation: a composer.phar next to the plugin
 * root wins (run through PHP), otherwise a PATH-installed composer.
 */
function composerCommand() {
	const phar = path.join( ROOT, 'composer.phar' );
	if ( fs.existsSync( phar ) ) {
		return { command: 'php', prefix: [ phar ] };
	}
	const names = process.platform === 'win32' ? [ 'composer.bat', 'composer.cmd', 'composer' ] : [ 'composer' ];
	for ( const name of names ) {
		const probe = spawnSync( name, [ '--version' ], { stdio: 'ignore' } );
		if ( ! probe.error && probe.status === 0 ) {
			return { command: name, prefix: [] };
		}
	}
	fail( 'Composer not found. Install Composer globally or place composer.phar in the plugin root (it is gitignored and never shipped).' );
}

function createZip() {
	const args = [ '-a', '-cf', ZIP_PATH, SLUG ];
	if ( process.platform === 'win32' ) {
		// Explicit System32 path so Git Bash's GNU tar can never shadow bsdtar.
		run( 'C:\\Windows\\System32\\tar.exe', args, { cwd: STAGE_ROOT } );
	} else if ( process.platform === 'darwin' ) {
		run( 'tar', args, { cwd: STAGE_ROOT } );
	} else {
		run( 'zip', [ '-rq', ZIP_PATH, SLUG ], { cwd: STAGE_ROOT } );
	}
}

if ( ! fs.existsSync( path.join( ROOT, 'build' ) ) ) {
	fail( 'build/ is missing — run `npm run build` (or `wp-scripts build`) first.' );
}

console.log( `[build-zip] Staging ${ SLUG } ${ VERSION }…` );
fs.rmSync( STAGE_ROOT, { recursive: true, force: true } );
fs.mkdirSync( STAGE_DIR, { recursive: true } );

for ( const item of INCLUDE ) {
	const source = path.join( ROOT, item );
	if ( ! fs.existsSync( source ) ) {
		fail( `${ item } is missing from the plugin root.` );
	}
	fs.cpSync( source, path.join( STAGE_DIR, item ), {
		recursive: true,
		filter: ( src ) => path.basename( src ) !== '.gitkeep',
	} );
}

console.log( '[build-zip] Installing production Composer dependencies…' );
const composer = composerCommand();
run(
	composer.command,
	[ ...composer.prefix, 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress' ],
	{ cwd: STAGE_DIR }
);
fs.rmSync( path.join( STAGE_DIR, 'composer.lock' ) );

console.log( '[build-zip] Creating zip…' );
fs.rmSync( ZIP_PATH, { force: true } );
createZip();
fs.rmSync( STAGE_ROOT, { recursive: true, force: true } );

const sizeMb = ( fs.statSync( ZIP_PATH ).size / ( 1024 * 1024 ) ).toFixed( 1 );
console.log( `[build-zip] Done: ${ path.relative( ROOT, ZIP_PATH ) } (${ sizeMb } MB)` );
