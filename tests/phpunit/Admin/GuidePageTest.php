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
				'__'          => function ( $text ) {
					return $text;
				},
				'esc_html__'  => function ( $text ) {
					return $text;
				},
				'esc_attr__'  => function ( $text ) {
					return $text;
				},
				'esc_html'    => function ( $text ) {
					return $text;
				},
				'esc_attr'    => function ( $text ) {
					return $text;
				},
				'esc_url'     => function ( $text ) {
					return $text;
				},
				'wp_kses'     => function ( $text ) {
					return $text;
				},
				'admin_url'   => function ( $path ) {
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
		Functions\expect( 'wp_enqueue_style' )->never();

		( new GuidePage() )->maybe_enqueue( 'woocommerce_page_wc-settings' );
		( new GuidePage() )->maybe_enqueue( 'toplevel_page_accessi-compliance-kit' );

		$this->addToAssertionCount( 1 );
	}

	public function test_maybe_enqueue_loads_styles_on_the_guide_page() {
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with( GuidePage::HANDLE, \Mockery::type( 'string' ), array(), ACCESSI_COMPLIANCE_KIT_VERSION );

		// Hook prefix derives from the (translated) parent menu title.
		( new GuidePage() )->maybe_enqueue( 'accessibility_page_' . GuidePage::MENU_SLUG );

		$this->addToAssertionCount( 1 );
	}

	public function test_render_page_outputs_all_sections_and_faq() {
		ob_start();
		( new GuidePage() )->render_page();
		$output = ob_get_clean();

		foreach ( array( 'Getting Started', 'Running a Scan', 'Understanding Results', 'The Six Fixes', 'Accessibility Statement', 'Notifications', 'Troubleshooting', 'Privacy & Data', 'For Developers', 'FAQ' ) as $section_title ) {
			$this->assertStringContainsString( $section_title, $output );
		}

		$this->assertStringContainsString( 'id="faq"', $output );
		$this->assertStringContainsString( '<details', $output );
		$this->assertStringContainsString( GuidePage::SUPPORT_URL, $output );
		$this->assertStringContainsString( 'admin.php?page=accessi-compliance-kit', $output );
	}

	public function test_render_page_lists_every_fix_from_the_registry() {
		ob_start();
		( new GuidePage() )->render_page();
		$output = ob_get_clean();

		foreach ( \AccessiComplianceKit\Fixes\FixManager::all_fixes() as $fix ) {
			$this->assertStringContainsString( $fix->label(), $output );
		}
	}
}
