<?php
/**
 * Registers the plugin's top-level admin menu and its WooCommerce pointer.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Utils\Capabilities;

/**
 * Adds the top-level "Accessibility" menu (logo icon; submenus: Dashboard,
 * User Guide — the latter registered by GuidePage) plus an "Accessibility"
 * link under WooCommerce pointing at the same page (proposal §4.1).
 * The page callback renders only a React mount point.
 */
class AdminMenu {

	const MENU_SLUG = 'accessi-compliance-kit';

	/**
	 * The universal-access figure drawn in a given color: outlined ring,
	 * head, and body with outstretched arms. Shared by the two icon builders.
	 *
	 * The ring is a filled even-odd annulus rather than a stroked circle:
	 * wp-admin's svg-painter.js recolors base64 menu icons by rewriting every
	 * fill attribute, so a `fill="none"` ring would become a solid disc.
	 *
	 * @var string
	 */
	const ICON_SHAPES = '<path fill="%1$s" fill-rule="evenodd" d="M12 .6A11.4 11.4 0 0 0 12 23.4 11.4'
		. ' 11.4 0 0 0 12 .6Zm0 1.6a9.8 9.8 0 0 1 0 19.6 9.8 9.8 0 0 1 0-19.6Z"/>'
		. '<circle cx="12" cy="6.9" r="2" fill="%1$s"/>'
		. '<path fill="%1$s" d="M17.6 9.4c-1.75.47-3.65.72-5.6.72s-3.85-.25-5.6-.72a.85.85 0 0 0-.44 1.64c1.44.39'
		. ' 2.94.63 4.44.71v2.06l-1.83 4.98a.85.85 0 0 0 1.6.59l1.72-4.68h.22l1.72 4.68a.85.85 0 0 0'
		. ' 1.6-.59l-1.83-4.98v-2.06c1.5-.08 3-.32 4.44-.71a.85.85 0 0 0-.44-1.64z"/>';

	/**
	 * Hook the admin menu registration.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	/**
	 * Register the top-level Accessibility menu, rename its first submenu
	 * item to "Dashboard", and add the pointer link under WooCommerce.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_menu_page(
			__( 'Accessibility', 'accessi-compliance-kit' ),
			__( 'Accessibility', 'accessi-compliance-kit' ),
			Capabilities::SCAN,
			self::MENU_SLUG,
			array( $this, 'render_page' ),
			self::menu_icon_data_uri(),
			'56.7'
		);

		// Same slug as the parent, so this becomes the first submenu item
		// (labelled "Dashboard") instead of WP duplicating "Accessibility".
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'accessi-compliance-kit' ),
			__( 'Dashboard', 'accessi-compliance-kit' ),
			Capabilities::SCAN,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);

		// Pointer under WooCommerce so store owners also find the plugin where
		// they expect WooCommerce tooling. A `.php?` slug renders as a plain
		// link to the top-level page rather than registering a second page.
		add_submenu_page(
			'woocommerce',
			__( 'Accessibility', 'accessi-compliance-kit' ),
			self::menu_icon_svg() . __( 'Accessibility', 'accessi-compliance-kit' ),
			Capabilities::SCAN,
			'admin.php?page=' . self::MENU_SLUG
		);
	}

	/**
	 * The plugin logo as a small inline SVG (universal-access figure).
	 *
	 * Marked `aria-hidden` so screen readers announce only the menu text.
	 * WordPress renders submenu titles unescaped, so the markup survives;
	 * sizing/alignment is inline because no plugin stylesheet loads globally
	 * in wp-admin.
	 *
	 * @param int    $size  Icon width/height in pixels.
	 * @param string $style Inline style; the default aligns the icon beside menu text.
	 * @return string
	 */
	public static function menu_icon_svg( $size = 14, $style = 'vertical-align:-2px;margin-right:6px;' ) {
		return sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="%1$d" height="%1$d"'
			. ' aria-hidden="true" focusable="false"%2$s>%3$s</svg>',
			(int) $size,
			'' === $style ? '' : ' style="' . esc_attr( $style ) . '"',
			sprintf( self::ICON_SHAPES, 'currentColor' )
		);
	}

	/**
	 * The logo as a data URI for `add_menu_page()`'s icon slot. Drawn in the
	 * default resting icon color; wp-admin's svg-painter.js then repaints it
	 * to match the active admin color scheme and hover/current states.
	 *
	 * @return string
	 */
	public static function menu_icon_data_uri() {
		$svg = sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">%s</svg>',
			sprintf( self::ICON_SHAPES, '#a7aaad' )
		);

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- WP's standard mechanism for SVG admin menu icons.
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Render the React mount point. All UI is rendered client-side.
	 *
	 * @return void
	 */
	public function render_page() {
		echo '<div id="accessi-compliance-kit-admin"></div>';
	}
}
