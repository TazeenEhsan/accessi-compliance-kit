/**
 * Admin app entry point. Mounts `App.jsx` into the root rendered by
 * `AdminMenu::render_page()`.
 */
import { createElement, createRoot } from '@wordpress/element';
import App from './App';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'accessi-compliance-kit-admin' );

	if ( ! container ) {
		return;
	}

	createRoot( container ).render( createElement( App ) );
} );
