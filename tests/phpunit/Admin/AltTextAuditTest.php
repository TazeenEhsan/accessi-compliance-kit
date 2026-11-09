<?php
/**
 * Tests for AltTextAudit.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Admin\AltTextAudit;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Admin\AltTextAudit
 */
class AltTextAuditTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'wp_strip_all_tags'   => function ( $text ) {
					return strip_tags( $text );
				},
				'sanitize_text_field' => function ( $text ) {
					return trim( $text );
				},
				'wp_slash'            => function ( $text ) {
					return addslashes( $text );
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_fill_one_writes_the_title_when_alt_is_empty() {
		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\expect( 'update_post_meta' )
			->once()
			->with( 12, '_wp_attachment_image_alt', 'Blue Mug' )
			->andReturn( 55 );

		$this->assertTrue( ( new AltTextAudit() )->fill_one( 12, ' <b>Blue Mug</b> ' ) );
	}

	public function test_fill_one_treats_whitespace_only_alt_as_empty() {
		Functions\when( 'get_post_meta' )->justReturn( '   ' );
		Functions\expect( 'update_post_meta' )->once()->andReturn( true );

		$this->assertTrue( ( new AltTextAudit() )->fill_one( 12, 'Blue Mug' ) );
	}

	public function test_fill_one_never_overwrites_existing_alt_text() {
		Functions\when( 'get_post_meta' )->justReturn( 'A blue ceramic mug' );
		Functions\expect( 'update_post_meta' )->never();

		$this->assertFalse( ( new AltTextAudit() )->fill_one( 12, 'Blue Mug' ) );
	}

	public function test_fill_one_skips_empty_titles_and_invalid_ids() {
		Functions\expect( 'update_post_meta' )->never();

		$audit = new AltTextAudit();

		$this->assertFalse( $audit->fill_one( 12, '   ' ) );
		$this->assertFalse( $audit->fill_one( 0, 'Blue Mug' ) );
	}
}
