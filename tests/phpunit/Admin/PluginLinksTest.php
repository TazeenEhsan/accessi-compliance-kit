<?php
/**
 * Tests for PluginLinks.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Admin\PluginLinks;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Admin\PluginLinks
 */
class PluginLinksTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'esc_html__' => function ( $text ) {
					return $text;
				},
				'esc_url'    => function ( $text ) {
					return $text;
				},
				'admin_url'  => function ( $path ) {
					return 'https://example.test/wp-admin/' . $path;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_action_links_are_prepended_and_existing_links_kept() {
		$links = ( new PluginLinks() )->add_action_links(
			array( 'deactivate' => '<a href="#">Deactivate</a>' )
		);

		$this->assertSame( array( 'dashboard', 'guide', 'deactivate' ), array_keys( $links ) );
		$this->assertStringContainsString( 'admin.php?page=accessibility-compliance-kit-for-woocommerce', $links['dashboard'] );
		$this->assertStringContainsString( '>Dashboard<', $links['dashboard'] );
		$this->assertStringContainsString( 'admin.php?page=accessibility-compliance-kit-for-woocommerce-guide', $links['guide'] );
		$this->assertStringContainsString( '>User Guide<', $links['guide'] );
	}

	public function test_register_hooks_the_plugin_row_filter() {
		Functions\when( 'plugin_basename' )->justReturn( 'accessibility-compliance-kit-for-woocommerce/accessibility-compliance-kit-for-woocommerce.php' );
		Monkey\Filters\expectAdded( 'plugin_action_links_accessibility-compliance-kit-for-woocommerce/accessibility-compliance-kit-for-woocommerce.php' )->once();

		( new PluginLinks() )->register();

		$this->addToAssertionCount( 1 );
	}
}
