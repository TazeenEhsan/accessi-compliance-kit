<?php
/**
 * Tests for GuidePage.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Admin\GuidePage;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Admin\GuidePage
 */
class GuidePageTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'__'        => function ( $text ) {
					return $text;
				},
				'admin_url' => function ( $path ) {
					return 'https://example.test/wp-admin/' . $path;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_add_menu_page_registers_under_the_plugin_top_level_menu() {
		Functions\expect( 'add_submenu_page' )
			->once()
			->with(
				\AccessiComplianceKit\Admin\AdminMenu::MENU_SLUG,
				\Mockery::type( 'string' ),
				'User Guide',
				'manage_options',
				GuidePage::MENU_SLUG,
				\Mockery::type( 'array' )
			);

		( new GuidePage() )->add_menu_page();

		$this->addToAssertionCount( 1 );
	}

	public function test_maybe_enqueue_skips_other_admin_pages() {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_enqueue_style' )->never();

		( new GuidePage() )->maybe_enqueue( 'woocommerce_page_wc-settings' );
		( new GuidePage() )->maybe_enqueue( 'toplevel_page_accessi-compliance-kit' );

		$this->addToAssertionCount( 1 );
	}

	public function test_maybe_enqueue_skips_when_the_built_bundle_is_missing() {
		// The bootstrap points ACCESSI_COMPLIANCE_KIT_PATH at a directory with
		// no build/, so the guard against a missing compiled bundle is hit.
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_enqueue_style' )->never();

		// Hook prefix derives from the (translated) parent menu title.
		( new GuidePage() )->maybe_enqueue( 'accessibility_page_' . GuidePage::MENU_SLUG );

		$this->addToAssertionCount( 1 );
	}

	public function test_render_page_outputs_the_react_mount_point() {
		ob_start();
		( new GuidePage() )->render_page();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="accessi-compliance-kit-guide-root"', $output );
		$this->assertStringContainsString( 'accessi-compliance-kit-guide', $output );
	}
}
