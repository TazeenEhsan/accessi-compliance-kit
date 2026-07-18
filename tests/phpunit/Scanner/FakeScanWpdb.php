<?php
/**
 * Minimal in-memory `$wpdb` double for ScanStorage round-trip tests.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stands in for `$wpdb`. `prepare()` bundles the query template with its args
 * (as JSON) instead of interpolating SQL, so `query()`/`get_row()`/`get_results()`
 * can read the args back directly rather than re-parsing a SQL string.
 */
class FakeScanWpdb {

	/**
	 * Row ID of the most recent INSERT, mirroring `$wpdb->insert_id`.
	 *
	 * @var int
	 */
	public $insert_id = 0;

	/**
	 * Table prefix, mirroring `$wpdb->prefix`.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * In-memory rows keyed by ID.
	 *
	 * @var array[]
	 */
	private $rows = array();

	/**
	 * Next auto-increment ID.
	 *
	 * @var int
	 */
	private $next_id = 1;

	/**
	 * Bundle a query template with its args.
	 *
	 * @param string $query Query template using %i/%s/%d placeholders.
	 * @param mixed  ...$args Placeholder values.
	 * @return string JSON-encoded {query, args} pair.
	 */
	public function prepare( $query, ...$args ) {
		if ( 1 === count( $args ) && is_array( $args[0] ) ) {
			$args = $args[0];
		}

		return json_encode( array( 'query' => $query, 'args' => $args ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}

	/**
	 * Execute an INSERT/UPDATE prepared "statement".
	 *
	 * @param string $prepared Value returned by prepare().
	 * @return int|false
	 */
	public function query( $prepared ) {
		list( $query, $args ) = $this->decode( $prepared );

		if ( 0 === strpos( $query, 'INSERT INTO' ) ) {
			$id                = $this->next_id++;
			$this->rows[ $id ] = array(
				'id'              => $id,
				'scan_type'       => $args[1],
				'url'             => $args[2],
				'started_at'      => $args[3],
				'completed_at'    => null,
				'status'          => $args[4],
				'violations_json' => null,
				'summary_json'    => null,
				'triggered_by'    => $args[5],
			);
			$this->insert_id   = $id;

			return 1;
		}

		if ( 0 === strpos( $query, 'UPDATE' ) && false !== strpos( $query, 'violations_json' ) ) {
			$id = (int) $args[5];

			if ( ! isset( $this->rows[ $id ] ) ) {
				return false;
			}

			$this->rows[ $id ]['status']          = $args[1];
			$this->rows[ $id ]['completed_at']     = $args[2];
			$this->rows[ $id ]['violations_json']  = $args[3];
			$this->rows[ $id ]['summary_json']     = $args[4];

			return 1;
		}

		if ( 0 === strpos( $query, 'UPDATE' ) ) {
			$id = (int) $args[4];

			if ( ! isset( $this->rows[ $id ] ) ) {
				return false;
			}

			$this->rows[ $id ]['status']       = $args[1];
			$this->rows[ $id ]['completed_at']  = $args[2];
			$this->rows[ $id ]['summary_json']  = $args[3];

			return 1;
		}

		return false;
	}

	/**
	 * Execute a single-row SELECT prepared "statement".
	 *
	 * @param string $prepared Value returned by prepare().
	 * @return array|null
	 */
	public function get_row( $prepared ) {
		list( , $args ) = $this->decode( $prepared );

		$id = (int) $args[1];

		return isset( $this->rows[ $id ] ) ? $this->rows[ $id ] : null;
	}

	/**
	 * Execute a multi-row SELECT prepared "statement" (ORDER BY started_at DESC LIMIT/OFFSET).
	 *
	 * @param string $prepared Value returned by prepare().
	 * @return array[]
	 */
	public function get_results( $prepared ) {
		list( , $args ) = $this->decode( $prepared );

		$limit  = (int) $args[1];
		$offset = (int) $args[2];

		$rows = array_values( $this->rows );

		usort(
			$rows,
			function ( $a, $b ) {
				return strcmp( $b['started_at'], $a['started_at'] );
			}
		);

		return array_slice( $rows, $offset, $limit );
	}

	/**
	 * Decode a prepared "statement" back into its template and args.
	 *
	 * @param string $prepared Value returned by prepare().
	 * @return array {0: string $query, 1: array $args}
	 */
	private function decode( $prepared ) {
		$decoded = json_decode( $prepared, true );

		return array( $decoded['query'], $decoded['args'] );
	}
}
