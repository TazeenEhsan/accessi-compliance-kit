<?php
/**
 * Registers active auto-fixes.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hard-coded registry of the six free fix classes (proposal §5.6). The only
 * place fixes are instantiated; loops on `init` and calls `register()` only
 * on fixes that are enabled.
 */
class FixManager {

	const FIX_CLASSES = array(
		ProductImageAltFix::class,
		CheckoutLabelsFix::class,
		FocusStatesFix::class,
		IconButtonAriaFix::class,
		PriceScreenReaderFix::class,
		EmptyLinkAnchorFix::class,
	);

	/**
	 * Hook fix registration and the `body_class` flag filter.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_enabled_fixes' ) );
		add_filter( 'body_class', array( $this, 'filter_body_class' ) );
	}

	/**
	 * Instantiate every fix and call `register()` on the ones that are enabled.
	 *
	 * @return void
	 */
	public function register_enabled_fixes() {
		foreach ( self::all_fixes() as $fix ) {
			if ( $fix->is_enabled() ) {
				$fix->register();
			}
		}
	}

	/**
	 * Append `accessibility-compliance-kit-for-woocommerce-fixes-active` plus a per-fix class for
	 * every enabled fix (proposal §5.4).
	 *
	 * @param string[] $classes Existing body classes.
	 * @return string[]
	 */
	public function filter_body_class( $classes ) {
		$any_enabled = false;

		foreach ( self::all_fixes() as $fix ) {
			if ( ! $fix->is_enabled() ) {
				continue;
			}

			$any_enabled = true;
			$classes[]   = 'accessibility-compliance-kit-for-woocommerce-fix-' . $fix->id();
		}

		if ( $any_enabled ) {
			$classes[] = 'accessibility-compliance-kit-for-woocommerce-fixes-active';
		}

		return $classes;
	}

	/**
	 * Instantiate all six free fixes.
	 *
	 * @return AbstractFix[]
	 */
	public static function all_fixes() {
		return array_map(
			function ( $fix_class ) {
				return new $fix_class();
			},
			self::FIX_CLASSES
		);
	}

	/**
	 * The stable IDs of all free fixes, used to whitelist settings input.
	 *
	 * @return string[]
	 */
	public static function fix_ids() {
		return array_map(
			function ( AbstractFix $fix ) {
				return $fix->id();
			},
			self::all_fixes()
		);
	}
}
