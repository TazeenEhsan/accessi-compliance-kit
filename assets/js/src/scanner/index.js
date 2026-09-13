/**
 * Scanner entry point. Only ever loaded by ScannerAssets when the scan flag
 * and capability check pass server-side (proposal §5.5 step 3). Runs axe-core
 * and reports results to the parent frame via a token-gated postMessage
 * (docs/security.md §5).
 */
import { runScan } from './runScan';

const settings = window.accessibilityComplianceKitForWooCommerceScanner || {};

/**
 * Post a scan outcome to the parent window, scoped to the admin origin.
 *
 * @param {Object} payload Message payload merged with the source/token envelope.
 * @return {void}
 */
function postToParent( payload ) {
	if ( ! settings.adminOrigin || window.parent === window ) {
		return;
	}

	window.parent.postMessage(
		Object.assign(
			{
				source: 'accessibility-compliance-kit-for-woocommerce-scanner',
				token: settings.handshakeToken,
			},
			payload
		),
		settings.adminOrigin
	);
}

/**
 * Run the scan and report success or failure to the parent window.
 *
 * @return {Promise<void>}
 */
async function init() {
	try {
		const violations = await runScan();

		postToParent( { status: 'complete', violations } );
	} catch ( error ) {
		postToParent( { status: 'error', message: error.message } );
	}
}

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
