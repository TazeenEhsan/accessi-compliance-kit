<?php
/**
 * Tests for ScanController.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Scanner\ScanController;
use AccessibilityComplianceKitForWooCommerce\Scanner\ScanStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Scanner\ScanController
 */
class ScanControllerTest extends TestCase {

	/**
	 * Fake `$wpdb` double backing a real ScanStorage instance.
	 *
	 * @var FakeScanWpdb
	 */
	private $wpdb;

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
	private $can_scan = true;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->wpdb    = new FakeScanWpdb();
		$this->options = array();
		$this->can_scan = true;

		global $wpdb;
		$wpdb = $this->wpdb; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		Functions\stubs(
			array(
				'current_time'         => function () {
					return gmdate( 'Y-m-d H:i:s', 1_752_753_600 );
				},
				'wp_json_encode'       => function ( $data ) {
					return json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				},
				'absint'               => function ( $value ) {
					return abs( (int) $value );
				},
				'wp_unslash'           => function ( $value ) {
					return $value;
				},
				'sanitize_text_field'  => function ( $value ) {
					return is_string( $value ) ? trim( $value ) : '';
				},
				'esc_url_raw'          => function ( $value ) {
					return is_string( $value ) ? $value : '';
				},
				'wp_parse_url'         => function ( $url, $component = -1 ) {
					return wp_parse_url_test_helper( $url, $component );
				},
				'home_url'             => function () {
					return 'https://example.test';
				},
				'get_current_user_id'  => function () {
					return 1;
				},
				'__'                   => function ( $text ) {
					return $text;
				},
			)
		);

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
				return $this->can_scan;
			}
		);

		Functions\when( 'wp_send_json_success' )->alias(
			function ( $data = null ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- test double, never rendered; carries the AJAX payload for assertions only.
				throw new AjaxResponseException( true, $data );
			}
		);
		Functions\when( 'wp_send_json_error' )->alias(
			function ( $data = null ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- test double, never rendered; carries the AJAX payload for assertions only.
				throw new AjaxResponseException( false, $data );
			}
		);
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_handle_run_scan_rejects_users_without_capability() {
		$this->can_scan = false;
		$controller     = new ScanController( new ScanStorage() );

		$_POST = array( 'url' => 'https://example.test/shop/' );

		try {
			$controller->handle_run_scan();
			$this->fail( 'Expected an AJAX error response.' );
		} catch ( AjaxResponseException $e ) {
			$this->assertFalse( $e->success );
		}
	}

	public function test_handle_run_scan_rejects_off_site_url() {
		$controller = new ScanController( new ScanStorage() );

		$_POST = array(
			'url'        => 'https://evil.example/',
			'violations' => '[]',
		);

		try {
			$controller->handle_run_scan();
			$this->fail( 'Expected an AJAX error response.' );
		} catch ( AjaxResponseException $e ) {
			$this->assertFalse( $e->success );
		}
	}

	public function test_handle_run_scan_rejects_malformed_violations_payload() {
		$controller = new ScanController( new ScanStorage() );

		$_POST = array(
			'url'        => 'https://example.test/shop/',
			'violations' => 'not-json',
		);

		try {
			$controller->handle_run_scan();
			$this->fail( 'Expected an AJAX error response.' );
		} catch ( AjaxResponseException $e ) {
			$this->assertFalse( $e->success );
		}
	}

	public function test_handle_run_scan_saves_and_returns_summary_on_success() {
		$controller = new ScanController( new ScanStorage() );

		$_POST = array(
			'url'        => 'https://example.test/shop/',
			'violations' => wp_json_encode(
				array(
					array(
						'rule'   => 'image-alt',
						'impact' => 'critical',
					),
				)
			),
		);

		try {
			$controller->handle_run_scan();
			$this->fail( 'Expected an AJAX success response.' );
		} catch ( AjaxResponseException $e ) {
			$this->assertTrue( $e->success );
			$this->assertSame( 'https://example.test/shop/', $e->data['url'] );
			$this->assertSame( 1, $e->data['summary']['critical'] );
			$this->assertSame( 1, $e->data['summary']['total'] );

			$scan = ( new ScanStorage() )->get_scan( $e->data['id'] );
			$this->assertSame( 'complete', $scan['status'] );
		}
	}

	public function test_handle_get_scan_returns_stored_scan() {
		$storage = new ScanStorage();
		$id      = $storage->create_scan( 'https://example.test/shop/', 'single', 1 );
		$storage->complete_scan( $id, array(), array( 'total' => 0 ) );

		$controller = new ScanController( $storage );
		$_POST      = array( 'id' => (string) $id );

		try {
			$controller->handle_get_scan();
			$this->fail( 'Expected an AJAX success response.' );
		} catch ( AjaxResponseException $e ) {
			$this->assertTrue( $e->success );
			$this->assertSame( $id, $e->data['id'] );
		}
	}

	public function test_handle_get_scan_errors_for_unknown_id() {
		$controller = new ScanController( new ScanStorage() );
		$_POST      = array( 'id' => '999' );

		try {
			$controller->handle_get_scan();
			$this->fail( 'Expected an AJAX error response.' );
		} catch ( AjaxResponseException $e ) {
			$this->assertFalse( $e->success );
		}
	}
}

/**
 * Minimal `wp_parse_url()` stand-in (real WP function is `parse_url()` plus quirks
 * we don't need for host comparisons in this test).
 *
 * @param string $url       URL to parse.
 * @param int    $component `PHP_URL_*` component constant, or -1 for all.
 * @return mixed
 */
function wp_parse_url_test_helper( $url, $component ) {
	return parse_url( $url, $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
}

/**
 * Thrown by the stubbed `wp_send_json_success()`/`wp_send_json_error()` so tests
 * can assert on the response without the real functions' `wp_die()` call.
 */
class AjaxResponseException extends \Exception {

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
