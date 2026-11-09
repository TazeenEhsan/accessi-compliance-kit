<?php
/**
 * Tests for SkipLinkFix.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\SkipLinkFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\SkipLinkFix
 */
class SkipLinkFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'__'         => function ( $text ) {
					return $text;
				},
				'esc_html__' => function ( $text ) {
					return $text;
				},
				'esc_attr'   => function ( $text ) {
					return $text;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_render_link_prints_a_hidden_link_with_the_stable_id() {
		ob_start();
		( new SkipLinkFix() )->render_link();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="tazeen-store-accessibility-kit-for-woocommerce-skip-link"', $output );
		$this->assertStringContainsString( ' hidden>', $output );
		$this->assertStringContainsString( 'Skip to main content', $output );
	}

	public function test_enqueue_adds_focus_reveal_css_and_bails_without_a_build() {
		Functions\expect( 'wp_register_style' )->once();
		Functions\expect( 'wp_enqueue_style' )->once();
		Functions\expect( 'wp_add_inline_style' )
			->once()
			->with(
				'tazeen-store-accessibility-kit-for-woocommerce-skip-link',
				\Mockery::pattern( '/:focus\{left:8px/' )
			);
		Functions\expect( 'wp_enqueue_script' )->never();

		( new SkipLinkFix() )->enqueue();

		$this->addToAssertionCount( 1 );
	}

	public function test_id_and_context() {
		$fix = new SkipLinkFix();

		$this->assertSame( 'skip_link', $fix->id() );
		$this->assertSame( array( 'global' ), $fix->applies_to() );
	}
}
