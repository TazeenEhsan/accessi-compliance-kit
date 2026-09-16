<?php
/**
 * Tests for FocusStatesFix.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\FocusStatesFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\FocusStatesFix
 */
class FocusStatesFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_enqueue_registers_the_frontend_fixes_stylesheet() {
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with(
				'tazeen-store-accessibility-kit-for-woocommerce-frontend-fixes',
				TSAKW_URL . 'assets/css/frontend-fixes.css',
				array(),
				TSAKW_VERSION
			);

		( new FocusStatesFix() )->enqueue();

		$this->addToAssertionCount( 1 );
	}

	public function test_applies_to_is_global() {
		$this->assertSame( array( 'global' ), ( new FocusStatesFix() )->applies_to() );
	}
}
