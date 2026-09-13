<?php
/**
 * Tests for IconButtonAriaFix.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\IconButtonAriaFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\IconButtonAriaFix
 */
class IconButtonAriaFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

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

	public function test_enqueue_bails_when_the_build_asset_is_missing() {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_localize_script' )->never();

		( new IconButtonAriaFix() )->enqueue();

		$this->addToAssertionCount( 1 );
	}

	public function test_applies_to_is_global() {
		$this->assertSame( array( 'global' ), ( new IconButtonAriaFix() )->applies_to() );
	}

	public function test_id_matches_the_active_fixes_option_key() {
		$this->assertSame( 'icon_button_aria', ( new IconButtonAriaFix() )->id() );
	}
}
