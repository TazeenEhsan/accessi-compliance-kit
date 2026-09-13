/**
 * Admin app entry point. Mounts `App.jsx` into the root rendered by
 * `AdminMenu::render_page()`.
 */
import { createElement, createRoot } from '@wordpress/element';
import App from './App';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'accessibility-compliance-kit-for-woocommerce-admin' );

	if ( ! container ) {
		return;
	}

	createRoot( container ).render( createElement( App ) );
} );
