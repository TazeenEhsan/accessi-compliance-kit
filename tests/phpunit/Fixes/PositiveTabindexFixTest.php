<?php
/**
 * Tests for PositiveTabindexFix.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\PositiveTabindexFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\PositiveTabindexFix
 */
class PositiveTabindexFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_enqueue_bails_when_the_build_asset_is_missing() {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_localize_script' )->never();

		( new PositiveTabindexFix() )->enqueue();

		$this->addToAssertionCount( 1 );
	}

	public function test_id_and_context() {
		$fix = new PositiveTabindexFix();

		$this->assertSame( 'positive_tabindex', $fix->id() );
		$this->assertSame( array( 'global' ), $fix->applies_to() );
	}
}
