<?php
/**
 * Tests for PriceScreenReaderFix.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\PriceScreenReaderFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\PriceScreenReaderFix
 */
class PriceScreenReaderFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'esc_attr'     => function ( $value ) {
					return $value;
				},
				'esc_html__'   => function ( $text ) {
					return $text;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_prefix_price_prepends_screen_reader_label() {
		$fix    = new PriceScreenReaderFix();
		$result = $fix->prefix_price( '<span class="woocommerce-Price-amount">$10.00</span>' );

		$this->assertStringContainsString( 'screen-reader-text', $result );
		$this->assertStringContainsString( 'Price:', $result );
		$this->assertStringContainsString( '$10.00', $result );
	}

	public function test_prefix_price_does_not_double_prefix() {
		$fix    = new PriceScreenReaderFix();
		$once   = $fix->prefix_price( '<span class="woocommerce-Price-amount">$10.00</span>' );
		$twice  = $fix->prefix_price( $once );

		$this->assertSame( $once, $twice );
		$this->assertSame( 1, substr_count( $twice, 'tazeen-store-accessibility-kit-for-woocommerce-price-label' ) );
	}

	public function test_prefix_price_leaves_empty_string_untouched() {
		$fix = new PriceScreenReaderFix();

		$this->assertSame( '', $fix->prefix_price( '' ) );
	}
}
