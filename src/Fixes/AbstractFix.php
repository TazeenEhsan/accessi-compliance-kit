<?php
/**
 * Base class every auto-fix extends.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Utils\Options;

/**
 * Contract for auto-fixes (proposal §5.6). Fixes never interact with each
 * other and must repair markup rather than hide violations (proposal §4.3).
 */
abstract class AbstractFix {

	/**
	 * Stable slug used as the settings key, e.g. `product_image_alt`.
	 *
	 * @return string
	 */
	abstract public function id();

	/**
	 * Translated label for the Settings UI.
	 *
	 * @return string
	 */
	abstract public function label();

	/**
	 * Translated one-line description for the Settings UI.
	 *
	 * @return string
	 */
	abstract public function description();

	/**
	 * Contexts this fix applies to: 'product', 'checkout', 'cart', 'global'.
	 *
	 * @return string[]
	 */
	abstract public function applies_to();

	/**
	 * Hook this fix's filters/actions. Called only when `is_enabled()` is true.
	 *
	 * @return void
	 */
	abstract public function register();

	/**
	 * Whether this fix is turned on in `accessibility_compliance_kit_for_woocommerce_active_fixes`.
	 * All fixes ship disabled (proposal §9); absence of the key means off.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$active_fixes = Options::get_active_fixes();

		return ! empty( $active_fixes[ $this->id() ] );
	}
}
