<?php
/**
 * Tests for AdminMenu.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Admin\AdminMenu;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Admin\AdminMenu
 */
class AdminMenuTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'__'       => function ( $text ) {
					return $text;
				},
				'esc_attr' => function ( $text ) {
					return $text;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_add_menu_page_registers_top_level_menu_with_svg_icon() {
		Functions\expect( 'add_menu_page' )
			->once()
			->with(
				'Accessibility',
				'Accessibility',
				'manage_options',
				AdminMenu::MENU_SLUG,
				\Mockery::type( 'array' ),
				\Mockery::on(
					function ( $icon ) {
						return 0 === strpos( $icon, 'data:image/svg+xml;base64,' );
					}
				),
				\Mockery::type( 'string' )
			);

		// First submenu renamed to "Dashboard" (same slug as parent), plus the
		// WooCommerce pointer link.
		Functions\expect( 'add_submenu_page' )
			->once()
			->with(
				AdminMenu::MENU_SLUG,
				'Dashboard',
				'Dashboard',
				'manage_options',
				AdminMenu::MENU_SLUG,
				\Mockery::type( 'array' )
			);
		Functions\expect( 'add_submenu_page' )
			->once()
			->with(
				'woocommerce',
				'Accessibility',
				\Mockery::on(
					function ( $menu_title ) {
						return false !== strpos( $menu_title, '<svg' )
							&& false !== strpos( $menu_title, 'Accessibility' );
					}
				),
				'manage_options',
				'admin.php?page=' . AdminMenu::MENU_SLUG
			);

		( new AdminMenu() )->add_menu_page();

		$this->addToAssertionCount( 1 );
	}

	public function test_menu_icon_data_uri_decodes_to_the_svg_logo() {
		$data_uri = AdminMenu::menu_icon_data_uri();

		$this->assertStringStartsWith( 'data:image/svg+xml;base64,', $data_uri );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- decoding our own icon to assert its content.
		$svg = base64_decode( substr( $data_uri, strlen( 'data:image/svg+xml;base64,' ) ) );

		$this->assertStringContainsString( '<svg', $svg );
		$this->assertStringContainsString( '#a7aaad', $svg );
	}

	public function test_render_page_outputs_the_react_mount_point() {
		ob_start();
		( new AdminMenu() )->render_page();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="tazeen-store-accessibility-kit-for-woocommerce-admin"', $output );
	}
}
