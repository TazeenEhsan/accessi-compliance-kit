<?php
/**
 * Debug logging wrapper.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin wrapper around `error_log()`, gated by `WP_DEBUG`.
 */
class Logger {

	/**
	 * Log a message when `WP_DEBUG` is enabled.
	 *
	 * @param mixed $message Message to log; arrays/objects are printed.
	 * @return void
	 */
	public static function log( $message ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- WP_DEBUG-gated wrapper is this class's stated purpose.
		error_log( '[accessibility-compliance-kit-for-woocommerce] ' . $message );
	}
}
