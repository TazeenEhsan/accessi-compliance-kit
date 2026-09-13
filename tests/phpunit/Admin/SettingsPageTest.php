<?php
/**
 * Tests for SettingsPage.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Admin\SettingsPage;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Admin\SettingsPage
 */
class SettingsPageTest extends TestCase {

	/**
	 * Options captured via the stubbed `update_option()`.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Capability granted to the stubbed current user for a given test.
	 *
	 * @var bool
	 */
	private $can_manage_settings = true;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->options              = array();
		$this->can_manage_settings = true;

		Functions\when( 'update_option' )->alias(
			function ( $key, $value ) {
				$this->options[ $key ] = $value;
				return true;
			}
		);
		Functions\when( 'get_option' )->alias(
			function ( $key, $default = false ) {
				return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
			}
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->alias(
			function () {
				return $this->can_manage_settings;
			}
		);
		Functions\when( 'absint' )->alias(
			function ( $value ) {
				return abs( (int) $value );
			}
		);
		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return $value;
			}
		);
		Functions\stubs(
			array(
				'__'             => function ( $text ) {
					return $text;
				},
				'wp_json_encode' => function ( $data ) {
					return json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				},
			)
		);

		Functions\when( 'wp_send_json_success' )->alias(
			function ( $data = null ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- test double, never rendered; carries the AJAX payload for assertions only.
				throw new SettingsAjaxResponseException( true, $data );
			}
		);
		Functions\when( 'wp_send_json_error' )->alias(
			function ( $data = null ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- test double, never rendered; carries the AJAX payload for assertions only.
				throw new SettingsAjaxResponseException( false, $data );
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_sanitize_active_fixes_whitelists_known_ids_and_casts_booleans() {
		$page = new SettingsPage();

		$result = $page->sanitize_active_fixes(
			array(
				'focus_states'    => '1',
				'checkout_labels' => 'true',
				'product_image_alt' => 'false',
				'unknown_fix_id'  => true,
			)
		);

		$this->assertSame(
			array(
				'product_image_alt'   => false,
				'checkout_labels'     => true,
				'focus_states'        => true,
				'icon_button_aria'    => false,
				'price_screen_reader' => false,
				'empty_link_anchor'   => false,
			),
			$result
		);
		$this->assertArrayNotHasKey( 'unknown_fix_id', $result );
	}

	public function test_sanitize_active_fixes_defaults_all_off_for_non_array_input() {
		$page   = new SettingsPage();
		$result = $page->sanitize_active_fixes( 'not-an-array' );

		$this->assertSame( array_fill_keys( array_keys( $result ), false ), $result );
	}

	public function test_sanitize_settings_casts_email_opt_in_and_page_id() {
		$page = new SettingsPage();

		$result = $page->sanitize_settings(
			array(
				'email_reminder_opt_in' => '1',
				'statement_page_id'     => '42',
				'unexpected_key'        => 'nope',
			)
		);

		$this->assertSame(
			array(
				'email_reminder_opt_in' => true,
				'statement_page_id'     => 42,
			),
			$result
		);
	}

	public function test_handle_save_settings_rejects_users_without_capability() {
		$this->can_manage_settings = false;
		$page                       = new SettingsPage();

		$_POST = array(
			'active_fixes'           => wp_json_encode( array( 'focus_states' => true ) ),
			'email_reminder_opt_in' => '1',
		);

		try {
			$page->handle_save_settings();
			$this->fail( 'Expected an AJAX error response.' );
		} catch ( SettingsAjaxResponseException $e ) {
			$this->assertFalse( $e->success );
		}
	}

	public function test_handle_save_settings_saves_and_returns_sanitized_data() {
		$page = new SettingsPage();

		$_POST = array(
			'active_fixes'           => wp_json_encode(
				array(
					'focus_states'   => true,
					'unknown_fix_id' => true,
				)
			),
			'email_reminder_opt_in' => '1',
		);

		try {
			$page->handle_save_settings();
			$this->fail( 'Expected an AJAX success response.' );
		} catch ( SettingsAjaxResponseException $e ) {
			$this->assertTrue( $e->success );
			$this->assertTrue( $e->data['active_fixes']['focus_states'] );
			$this->assertFalse( $e->data['active_fixes']['product_image_alt'] );
			$this->assertArrayNotHasKey( 'unknown_fix_id', $e->data['active_fixes'] );
			$this->assertTrue( $e->data['settings']['email_reminder_opt_in'] );

			$this->assertSame( $e->data['active_fixes'], $this->options['accessibility_compliance_kit_for_woocommerce_active_fixes'] );
			$this->assertSame( $e->data['settings'], $this->options['accessibility_compliance_kit_for_woocommerce_settings'] );
		}
	}

	public function test_handle_save_settings_defaults_fixes_off_for_malformed_json() {
		$page = new SettingsPage();

		$_POST = array(
			'active_fixes'           => 'not-json',
			'email_reminder_opt_in' => '0',
		);

		try {
			$page->handle_save_settings();
			$this->fail( 'Expected an AJAX success response.' );
		} catch ( SettingsAjaxResponseException $e ) {
			$this->assertTrue( $e->success );
			$this->assertFalse( $e->data['active_fixes']['focus_states'] );
			$this->assertFalse( $e->data['settings']['email_reminder_opt_in'] );
		}
	}
}

/**
 * Thrown by the stubbed `wp_send_json_success()`/`wp_send_json_error()` so tests
 * can assert on the response without the real functions' `wp_die()` call.
 */
class SettingsAjaxResponseException extends \Exception {

	/**
	 * Whether this represents a success response.
	 *
	 * @var bool
	 */
	public $success;

	/**
	 * Response payload.
	 *
	 * @var mixed
	 */
	public $data;

	/**
	 * Constructor.
	 *
	 * @param bool  $success Whether this represents a success response.
	 * @param mixed $data    Response payload.
	 */
	public function __construct( $success, $data ) {
		parent::__construct( 'AJAX response' );
		$this->success = $success;
		$this->data    = $data;
	}
}
