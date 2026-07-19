/**
 * Guide app entry point. Mounts `App.jsx` into the root rendered by
 * `GuidePage::render_page()`.
 */
import { createElement, createRoot } from '@wordpress/element';
import App from './App';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'accessi-compliance-kit-guide-root' );

	if ( ! container ) {
		return;
	}

	createRoot( container ).render( createElement( App ) );
} );
