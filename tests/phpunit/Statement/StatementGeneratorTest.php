<?php
/**
 * Tests for StatementGenerator.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Statement;

use AccessiComplianceKit\Statement\StatementGenerator;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Statement\StatementGenerator
 */
class StatementGeneratorTest extends TestCase {

	/**
	 * Options captured via the stubbed `update_option()`.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Post types keyed by post ID, standing in for `get_post_type()` lookups.
	 *
	 * @var array
	 */
	private $post_types;

	/**
	 * Next post ID `wp_insert_post()` will hand out.
	 *
	 * @var int
	 */
	private $next_post_id;

	/**
	 * Capability granted to the stubbed current user for a given test.
	 *
	 * @var bool
	 */
	private $can_manage_settings = true;

	/**
	 * When set, `wp_insert_post()` returns this instead of a new post ID.
	 *
	 * @var \WP_Error|null
	 */
	private $insert_post_error = null;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->options              = array();
		$this->post_types           = array();
		$this->next_post_id         = 100;
		$this->can_manage_settings  = true;
		$this->insert_post_error    = null;

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

		Functions\when( 'wp_insert_post' )->alias(
			function ( $postarr ) {
				if ( null !== $this->insert_post_error ) {
					return $this->insert_post_error;
				}

				$id                       = $this->next_post_id++;
				$this->post_types[ $id ]  = $postarr['post_type'];
				return $id;
			}
		);
		Functions\when( 'is_wp_error' )->alias(
			function ( $thing ) {
				return $thing instanceof \WP_Error;
			}
		);
		Functions\when( 'get_post_type' )->alias(
			function ( $id ) {
				return isset( $this->post_types[ $id ] ) ? $this->post_types[ $id ] : false;
			}
		);
		Functions\when( 'get_edit_post_link' )->alias(
			function ( $id ) {
				return 'https://example.test/wp-admin/post.php?post=' . $id . '&action=edit';
			}
		);

		Functions\stubs(
			array(
				'absint'          => function ( $value ) {
					return abs( (int) $value );
				},
				'wp_unslash'      => function ( $value ) {
					return $value;
				},
				'__'              => function ( $text ) {
					return $text;
				},
				'esc_html__'      => function ( $text ) {
					return $text;
				},
				'esc_html'        => function ( $text ) {
					return $text;
				},
				'get_bloginfo'    => function () {
					return 'Example Store';
				},
				'date_i18n'       => function () {
					return '2026-07-17';
				},
			)
		);

		Functions\when( 'wp_send_json_success' )->alias(
			function ( $data = null ) {
				throw new StatementAjaxResponseException( true, $data );
			}
		);
		Functions\when( 'wp_send_json_error' )->alias(
			function ( $data = null ) {
				throw new StatementAjaxResponseException( false, $data );
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_generate_creates_page_and_stores_page_id_when_none_exists() {
		$generator = new StatementGenerator();

		$result = $generator->generate();

		$this->assertSame( 'created', $result['status'] );
		$this->assertSame( 100, $result['pageId'] );
		$this->assertSame( 100, $this->options['accessi_compliance_kit_settings']['statement_page_id'] );
	}

	public function test_generate_warns_instead_of_duplicating_when_a_page_already_exists() {
		$generator = new StatementGenerator();
		$generator->generate();

		$result = $generator->generate();

		$this->assertSame( 'exists', $result['status'] );
		$this->assertSame( 100, $result['pageId'] );
	}

	public function test_generate_creates_a_new_page_when_force_new_is_requested() {
		$generator = new StatementGenerator();
		$generator->generate();

		$result = $generator->generate( true );

		$this->assertSame( 'created', $result['status'] );
		$this->assertSame( 101, $result['pageId'] );
		$this->assertSame( 101, $this->options['accessi_compliance_kit_settings']['statement_page_id'] );
	}

	public function test_generate_treats_a_deleted_statement_page_as_absent() {
		$this->options['accessi_compliance_kit_settings'] = array( 'statement_page_id' => 999 );

		$generator = new StatementGenerator();
		$result    = $generator->generate();

		$this->assertSame( 'created', $result['status'] );
	}

	public function test_generate_returns_error_status_when_insert_fails() {
		$this->insert_post_error = new \WP_Error( 'db_error', 'Could not insert page.' );

		$generator = new StatementGenerator();
		$result    = $generator->generate();

		$this->assertSame( 'error', $result['status'] );
		$this->assertSame( 'Could not insert page.', $result['message'] );
	}

	public function test_handle_generate_statement_rejects_users_without_capability() {
		$this->can_manage_settings = false;
		$generator                 = new StatementGenerator();

		$_POST = array();

		try {
			$generator->handle_generate_statement();
			$this->fail( 'Expected an AJAX error response.' );
		} catch ( StatementAjaxResponseException $e ) {
			$this->assertFalse( $e->success );
		}
	}

	public function test_handle_generate_statement_returns_created_page_on_success() {
		$generator = new StatementGenerator();
		$_POST     = array();

		try {
			$generator->handle_generate_statement();
			$this->fail( 'Expected an AJAX success response.' );
		} catch ( StatementAjaxResponseException $e ) {
			$this->assertTrue( $e->success );
			$this->assertSame( 'created', $e->data['status'] );
			$this->assertSame( 100, $e->data['pageId'] );
		}
	}

	public function test_handle_generate_statement_passes_through_force_new() {
		$generator = new StatementGenerator();

		$_POST = array();
		try {
			$generator->handle_generate_statement();
		} catch ( StatementAjaxResponseException $e ) {
			unset( $e );
		}

		$_POST = array( 'force_new' => '1' );

		try {
			$generator->handle_generate_statement();
			$this->fail( 'Expected an AJAX success response.' );
		} catch ( StatementAjaxResponseException $e ) {
			$this->assertTrue( $e->success );
			$this->assertSame( 'created', $e->data['status'] );
			$this->assertSame( 101, $e->data['pageId'] );
		}
	}
}

/**
 * Thrown by the stubbed `wp_send_json_success()`/`wp_send_json_error()` so tests
 * can assert on the response without the real functions' `wp_die()` call.
 */
class StatementAjaxResponseException extends \Exception {

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
