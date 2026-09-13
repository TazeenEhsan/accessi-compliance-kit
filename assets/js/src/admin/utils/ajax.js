/**
 * Small `admin-ajax.php` POST helper shared by the admin app's components.
 */

import { __ } from '@wordpress/i18n';

/**
 * Post an `admin-ajax.php` action and resolve with its `data` payload.
 *
 * @param {string} ajaxUrl Localized `admin-ajax.php` URL.
 * @param {string} action  The `wp_ajax_*` action name.
 * @param {string} nonce   Per-action nonce.
 * @param {Object} data    Additional POST fields.
 * @return {Promise<Object>} Resolves with `response.data` on success.
 */
export async function ajaxRequest( ajaxUrl, action, nonce, data = {} ) {
	const body = new URLSearchParams( { action, nonce, ...data } );

	const response = await window.fetch( ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body,
	} );

	const json = await response.json();

	if ( ! json.success ) {
		const message = json.data && json.data.message ? json.data.message : __( 'Request failed.', 'accessibility-compliance-kit-for-woocommerce' );
		throw new Error( message );
	}

	return json.data;
}
