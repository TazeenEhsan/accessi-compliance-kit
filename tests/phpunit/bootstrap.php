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

if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 7 * 24 * 60 * 60 );
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

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Minimal stand-in for WordPress core's `WP_Error` so failure paths
	 * (e.g. `wp_insert_post()` returning an error) can be exercised.
	 */
	class WP_Error { // phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound, PSR1.Classes.ClassDeclaration.MultipleClasses

		/**
		 * Error message passed to the constructor.
		 *
		 * @var string
		 */
		private $message;

		/**
		 * Constructor.
		 *
		 * @param string $code    Unused; kept for signature compatibility.
		 * @param string $message Error message.
		 */
		public function __construct( $code = '', $message = '' ) {
			$this->message = $message;
		}

		/**
		 * Get the error message.
		 *
		 * @return string
		 */
		public function get_error_message() {
			return $this->message;
		}
	}
}

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';
