<?php
/**
 * PHPUnit bootstrap. Uses Brain Monkey to stub WordPress functions rather than
 * booting a full WP install (docs/coding-guidelines.md §4).
 *
 * @package AccessiComplianceKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! defined( 'ACCESSI_COMPLIANCE_KIT_PATH' ) ) {
	// Deliberately not the real plugin root: this directory has no `build/`
	// subfolder, so `file_exists()` checks against compiled assets are
	// deterministically false regardless of whether `npm run build` has run.
	define( 'ACCESSI_COMPLIANCE_KIT_PATH', __DIR__ . '/' );
}

if ( ! defined( 'ACCESSI_COMPLIANCE_KIT_URL' ) ) {
	define( 'ACCESSI_COMPLIANCE_KIT_URL', 'https://example.test/wp-content/plugins/accessi-compliance-kit/' );
}

if ( ! defined( 'ACCESSI_COMPLIANCE_KIT_VERSION' ) ) {
	define( 'ACCESSI_COMPLIANCE_KIT_VERSION', '0.1.0-test' );
}

if ( ! class_exists( 'WC_Product' ) ) {
	/**
	 * Minimal stand-in for WooCommerce's `WC_Product` so fix classes' `instanceof`
	 * checks can be exercised without requiring a WooCommerce checkout.
	 */
	class WC_Product { // phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound, PSR1.Classes.ClassDeclaration.MultipleClasses
	}
}

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';
