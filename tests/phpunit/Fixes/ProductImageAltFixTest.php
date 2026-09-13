<?php
/**
 * Tests for ProductImageAltFix.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\ProductImageAltFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\ProductImageAltFix
 */
class ProductImageAltFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'esc_attr' => function ( $value ) {
					return $value;
				},
				'__'       => function ( $text ) {
					return $text;
				},
			)
		);
	}

	protected function tearDown(): void {
		global $product, $post;
		$product = null; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- simulates WooCommerce's own global $product, not a plugin-owned global.
		$post    = null;

		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_maybe_add_alt_leaves_existing_alt_untouched() {
		global $product;
		$product = new FakeWcProduct( 5, array(), 'Blue Mug' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- simulates WooCommerce's own global $product, not a plugin-owned global.

		$fix   = new ProductImageAltFix();
		$attr  = array( 'alt' => 'Custom alt' );
		$image = (object) array( 'ID' => 5 );

		$result = $fix->maybe_add_alt( $attr, $image );

		$this->assertSame( 'Custom alt', $result['alt'] );
	}

	public function test_maybe_add_alt_fills_empty_alt_from_featured_image() {
		global $product;
		$product = new FakeWcProduct( 5, array(), 'Blue Mug' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- simulates WooCommerce's own global $product, not a plugin-owned global.

		$fix   = new ProductImageAltFix();
		$attr  = array( 'alt' => '' );
		$image = (object) array( 'ID' => 5 );

		$result = $fix->maybe_add_alt( $attr, $image );

		$this->assertSame( 'Blue Mug', $result['alt'] );
	}

	public function test_maybe_add_alt_fills_empty_alt_from_gallery_image() {
		global $product;
		$product = new FakeWcProduct( 5, array( 6, 7 ), 'Blue Mug' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- simulates WooCommerce's own global $product, not a plugin-owned global.

		$fix   = new ProductImageAltFix();
		$attr  = array();
		$image = (object) array( 'ID' => 7 );

		$result = $fix->maybe_add_alt( $attr, $image );

		$this->assertSame( 'Blue Mug', $result['alt'] );
	}

	public function test_maybe_add_alt_leaves_unrelated_image_untouched() {
		global $product;
		$product = new FakeWcProduct( 5, array( 6 ), 'Blue Mug' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- simulates WooCommerce's own global $product, not a plugin-owned global.

		$fix   = new ProductImageAltFix();
		$attr  = array();
		$image = (object) array( 'ID' => 999 );

		$result = $fix->maybe_add_alt( $attr, $image );

		$this->assertArrayNotHasKey( 'alt', $result );
	}
}

/**
 * Minimal `WC_Product` test double.
 */
class FakeWcProduct extends \WC_Product {

	private $image_id;
	private $gallery_ids;
	private $name;

	public function __construct( $image_id, array $gallery_ids, $name ) {
		$this->image_id    = $image_id;
		$this->gallery_ids = $gallery_ids;
		$this->name        = $name;
	}

	public function get_image_id() {
		return $this->image_id;
	}

	public function get_gallery_image_ids() {
		return $this->gallery_ids;
	}

	public function get_name() {
		return $this->name;
	}
}
