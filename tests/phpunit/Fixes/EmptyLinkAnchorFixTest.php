<?php
/**
 * Tests for EmptyLinkAnchorFix.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Fixes;

use AccessiComplianceKit\Fixes\EmptyLinkAnchorFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Fixes\EmptyLinkAnchorFix
 */
class EmptyLinkAnchorFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'esc_html' => function ( $value ) {
					return $value;
				},
				'__'       => function ( $text ) {
					return $text;
				},
			)
		);
	}

	protected function tearDown(): void {
		global $product;
		$product = null;

		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_inject_accessible_name_prints_the_product_title() {
		global $product;
		$product = new FakeWcProductForEmptyLink( 'Red Mug' );

		$fix = new EmptyLinkAnchorFix();

		ob_start();
		$fix->inject_accessible_name();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'screen-reader-text', $output );
		$this->assertStringContainsString( 'Red Mug', $output );
	}

	public function test_inject_accessible_name_bails_without_a_product() {
		global $product;
		$product = null;

		$fix = new EmptyLinkAnchorFix();

		ob_start();
		$fix->inject_accessible_name();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_enqueue_bails_when_the_build_asset_is_missing() {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_localize_script' )->never();

		( new EmptyLinkAnchorFix() )->enqueue();

		$this->addToAssertionCount( 1 );
	}

	public function test_applies_to_includes_product_and_global() {
		$this->assertSame( array( 'product', 'global' ), ( new EmptyLinkAnchorFix() )->applies_to() );
	}
}

/**
 * Minimal `WC_Product` test double.
 */
class FakeWcProductForEmptyLink extends \WC_Product {

	private $name;

	public function __construct( $name ) {
		$this->name = $name;
	}

	public function get_name() {
		return $this->name;
	}
}
