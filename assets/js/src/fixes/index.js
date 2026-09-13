/**
 * Shared front-end auto-fix bundle for IconButtonAriaFix and EmptyLinkAnchorFix
 * (docs/frontend.md §4.4, §4.6). Dependency-free, a few KB, enqueued only when
 * one of those two fixes is enabled. Repairs real markup by adding missing
 * accessible names — never hides anything (proposal §4.3).
 */

const ICON_BUTTON_SELECTORS = [
	{ selector: 'a.cart-contents, .site-header-cart a, a[href*="cart"].icon, .cart-icon a', labelKey: 'cart' },
	{ selector: '.search-toggle, button.search-icon, a.search-icon, .site-search .icon-search', labelKey: 'search' },
	{ selector: '.add_to_wishlist, .yith-wcwl-add-to-wishlist a, .wishlist-icon', labelKey: 'wishlist' },
];

/**
 * Whether an element already exposes an accessible name.
 *
 * @param {Element} el Element to check.
 * @return {boolean}
 */
function hasAccessibleName( el ) {
	if ( el.getAttribute( 'aria-label' ) || el.getAttribute( 'aria-labelledby' ) ) {
		return true;
	}

	if ( el.textContent && el.textContent.trim().length > 0 ) {
		return true;
	}

	if ( el.title && el.title.trim().length > 0 ) {
		return true;
	}

	const img = el.querySelector( 'img[alt]' );

	return !! ( img && img.getAttribute( 'alt' ) && img.getAttribute( 'alt' ).trim().length > 0 );
}

/**
 * Add `aria-label` to recognized icon-only cart/search/wishlist controls
 * that currently have no accessible name.
 *
 * @param {Object} labels Translated labels keyed by `cart`/`search`/`wishlist`.
 */
function fixIconButtons( labels ) {
	ICON_BUTTON_SELECTORS.forEach( ( { selector, labelKey } ) => {
		const label = labels && labels[ labelKey ];

		if ( ! label ) {
			return;
		}

		document.querySelectorAll( selector ).forEach( ( el ) => {
			if ( ! hasAccessibleName( el ) ) {
				el.setAttribute( 'aria-label', label );
			}
		} );
	} );
}

/**
 * Add an `aria-label` to empty link anchors that a server-side filter
 * couldn't reach, falling back to an image's alt text when present.
 *
 * @param {string} fallbackText Generic fallback label.
 */
function fixEmptyAnchors( fallbackText ) {
	document.querySelectorAll( 'a[href]' ).forEach( ( anchor ) => {
		if ( hasAccessibleName( anchor ) ) {
			return;
		}

		const img = anchor.querySelector( 'img' );
		const altText = img && img.getAttribute( 'alt' );
		const label = ( altText && altText.trim() ) || fallbackText;

		if ( label ) {
			anchor.setAttribute( 'aria-label', label );
		}
	} );
}

function run() {
	const iconButtonAria = window.accessibilityComplianceKitForWooCommerceIconButtonAria;
	const emptyLinkAnchor = window.accessibilityComplianceKitForWooCommerceEmptyLinkAnchor;

	if ( iconButtonAria && iconButtonAria.enabled ) {
		fixIconButtons( iconButtonAria.labels );
	}

	if ( emptyLinkAnchor && emptyLinkAnchor.enabled ) {
		fixEmptyAnchors( emptyLinkAnchor.fallbackText );
	}
}

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', run );
} else {
	run();
}
