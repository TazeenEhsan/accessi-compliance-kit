/**
 * Shared front-end auto-fix bundle for IconButtonAriaFix, EmptyLinkAnchorFix,
 * SkipLinkFix and PositiveTabindexFix (docs/frontend.md §4.4, §4.6).
 * Dependency-free, a few KB, enqueued only when one of those fixes is enabled.
 * Repairs real markup — never hides anything (proposal §4.3).
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

const MAIN_SELECTORS = '#main, #content, #primary, main, [role="main"]';

/**
 * Whether the page already exposes a skip link of its own: an in-page
 * "skip ..." anchor among the first few links of the document.
 *
 * @param {Element} ownLink Our own (hidden) skip link, ignored here.
 * @return {boolean}
 */
function themeHasSkipLink( ownLink ) {
	const links = Array.from( document.querySelectorAll( 'a[href^="#"]' ) )
		.filter( ( a ) => a !== ownLink )
		.slice( 0, 5 );

	return links.some( ( a ) => {
		const target = a.getAttribute( 'href' );
		const text = ( a.textContent || '' ).toLowerCase();

		return target.length > 1 && /\bskip\b/.test( text );
	} );
}

/**
 * Reveal the server-rendered skip link only when a main landmark exists and
 * the theme has no skip link of its own; otherwise remove it. The target is
 * made programmatically focusable so focus actually moves there.
 *
 * @param {string} linkId ID of the server-rendered link.
 */
function setupSkipLink( linkId ) {
	const link = document.getElementById( linkId );

	if ( ! link ) {
		return;
	}

	const target = document.querySelector( MAIN_SELECTORS );

	if ( ! target || themeHasSkipLink( link ) ) {
		link.remove();
		return;
	}

	if ( ! target.id ) {
		target.id = linkId + '-target';
	}

	if ( ! target.hasAttribute( 'tabindex' ) ) {
		target.setAttribute( 'tabindex', '-1' );
	}

	link.setAttribute( 'href', '#' + target.id );
	link.hidden = false;
}

/**
 * Reset a single element's positive tabindex to 0, remembering the original.
 *
 * @param {Element} el Element to check.
 */
function resetTabindex( el ) {
	const value = parseInt( el.getAttribute( 'tabindex' ), 10 );

	if ( value > 0 ) {
		el.setAttribute( 'data-tsakw-original-tabindex', String( value ) );
		el.setAttribute( 'tabindex', '0' );
	}
}

/**
 * Reset positive tabindex values now and on later DOM changes (menus, quick
 * views and other scripts often add them after load).
 */
function fixPositiveTabindex() {
	const scan = ( root ) => {
		if ( root.nodeType !== 1 ) {
			return;
		}

		resetTabindex( root );
		root.querySelectorAll( '[tabindex]' ).forEach( resetTabindex );
	};

	scan( document.documentElement );

	if ( 'function' !== typeof window.MutationObserver ) {
		return;
	}

	new window.MutationObserver( ( mutations ) => {
		mutations.forEach( ( mutation ) => {
			if ( 'attributes' === mutation.type ) {
				resetTabindex( mutation.target );
			} else {
				mutation.addedNodes.forEach( scan );
			}
		} );
	} ).observe( document.documentElement, {
		subtree: true,
		childList: true,
		attributes: true,
		attributeFilter: [ 'tabindex' ],
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

	const skipLink = window.accessibilityComplianceKitForWooCommerceSkipLink;
	const positiveTabindex = window.accessibilityComplianceKitForWooCommercePositiveTabindex;

	if ( skipLink && skipLink.enabled ) {
		setupSkipLink( skipLink.linkId );
	}

	if ( positiveTabindex && positiveTabindex.enabled ) {
		fixPositiveTabindex();
	}
}

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', run );
} else {
	run();
}
