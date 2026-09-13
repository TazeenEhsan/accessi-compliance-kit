<?php
/**
 * Tests for FixManager.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\FixManager;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\FixManager
 */
class FixManagerTest extends TestCase {

	/**
	 * Stubbed `accessibility_compliance_kit_for_woocommerce_active_fixes` option value for the test.
	 *
	 * @var array
	 */
	private $active_fixes;

	/**
	 * Hooks registered via the stubbed `add_action()`/`add_filter()`.
	 *
	 * @var string[]
	 */
	private $registered_hooks;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->active_fixes    = array();
		$this->registered_hooks = array();

		Functions\when( 'get_option' )->alias(
			function ( $key, $default = false ) {
				return 'accessibility_compliance_kit_for_woocommerce_active_fixes' === $key ? $this->active_fixes : $default;
			}
		);

		Functions\when( 'add_action' )->alias(
			function ( $hook ) {
				$this->registered_hooks[] = $hook;
			}
		);
		Functions\when( 'add_filter' )->alias(
			function ( $hook ) {
				$this->registered_hooks[] = $hook;
			}
		);

		Functions\stubs(
			array(
				'__' => function ( $text ) {
					return $text;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_register_enabled_fixes_only_hooks_the_enabled_fix() {
		$this->active_fixes = array( 'focus_states' => true );

		( new FixManager() )->register_enabled_fixes();

		$this->assertContains( 'wp_enqueue_scripts', $this->registered_hooks );
		$this->assertNotContains( 'woocommerce_form_field_args', $this->registered_hooks );
		$this->assertNotContains( 'wp_get_attachment_image_attributes', $this->registered_hooks );
		$this->assertNotContains( 'woocommerce_get_price_html', $this->registered_hooks );
	}

	public function test_register_enabled_fixes_hooks_nothing_when_all_disabled() {
		$this->active_fixes = array();

		( new FixManager() )->register_enabled_fixes();

		$this->assertSame( array(), $this->registered_hooks );
	}

	public function test_filter_body_class_adds_classes_only_for_enabled_fixes() {
		$this->active_fixes = array(
			'focus_states'    => true,
			'checkout_labels' => false,
		);

		$classes = ( new FixManager() )->filter_body_class( array( 'existing' ) );

		$this->assertContains( 'accessibility-compliance-kit-for-woocommerce-fix-focus_states', $classes );
		$this->assertContains( 'accessibility-compliance-kit-for-woocommerce-fixes-active', $classes );
		$this->assertNotContains( 'accessibility-compliance-kit-for-woocommerce-fix-checkout_labels', $classes );
	}

	public function test_filter_body_class_leaves_classes_untouched_when_none_enabled() {
		$this->active_fixes = array();

		$classes = ( new FixManager() )->filter_body_class( array( 'existing' ) );

		$this->assertSame( array( 'existing' ), $classes );
	}

	public function test_fix_ids_lists_all_six_free_fixes() {
		$this->assertSame(
			array(
				'product_image_alt',
				'checkout_labels',
				'focus_states',
				'icon_button_aria',
				'price_screen_reader',
				'empty_link_anchor',
			),
			FixManager::fix_ids()
		);
	}
}
