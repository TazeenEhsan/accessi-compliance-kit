<?php
/**
 * Persists and retrieves scan rows in `{$wpdb->prefix}accessi_compliance_kit_scans`.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Utils\Logger;
use AccessiComplianceKit\Utils\Options;

/**
 * The only class that reads/writes `wp_accessi_compliance_kit_scans` (AI_RULES §6).
 */
class ScanStorage {

	const STATUS_RUNNING  = 'running';
	const STATUS_COMPLETE = 'complete';
	const STATUS_FAILED   = 'failed';

	/**
	 * Insert a new scan row in the `running` state.
	 *
	 * @param string $url     Page URL being scanned.
	 * @param string $type    Scan type: 'single' (free) or 'crawl' (Pro).
	 * @param int    $user_id WP user ID that triggered the scan.
	 * @return int|false Inserted row ID, or false on failure.
	 */
	public function create_scan( $url, $type, $user_id ) {
		global $wpdb;

		$result = $wpdb->query(
			$wpdb->prepare(
				'INSERT INTO %i ( scan_type, url, started_at, status, triggered_by ) VALUES ( %s, %s, %s, %s, %d )',
				$this->table(),
				$type,
				$url,
				current_time( 'mysql', true ),
				self::STATUS_RUNNING,
				$user_id
			)
		);

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Mark a scan complete and store its results.
	 *
	 * @param int   $id         Scan row ID.
	 * @param array $violations Normalized violations (docs/database.md §4).
	 * @param array $summary    Severity summary counts.
	 * @return bool
	 */
	public function complete_scan( $id, array $violations, array $summary ) {
		global $wpdb;

		$result = $wpdb->query(
			$wpdb->prepare(
				'UPDATE %i SET status = %s, completed_at = %s, violations_json = %s, summary_json = %s WHERE id = %d',
				$this->table(),
				self::STATUS_COMPLETE,
				current_time( 'mysql', true ),
				wp_json_encode( $violations ),
				wp_json_encode( $summary ),
				$id
			)
		);

		if ( false === $result ) {
			return false;
		}

		Options::update_last_scan_id( $id );

		return true;
	}

	/**
	 * Mark a scan failed.
	 *
	 * @param int    $id     Scan row ID.
	 * @param string $reason Optional failure reason, stored inside `summary_json`.
	 * @return bool
	 */
	public function fail_scan( $id, $reason = '' ) {
		global $wpdb;

		$summary = array();

		if ( '' !== $reason ) {
			$summary['reason'] = $reason;
		}

		$result = $wpdb->query(
			$wpdb->prepare(
				'UPDATE %i SET status = %s, completed_at = %s, summary_json = %s WHERE id = %d',
				$this->table(),
				self::STATUS_FAILED,
				current_time( 'mysql', true ),
				wp_json_encode( $summary ),
				$id
			)
		);

		return false !== $result;
	}

	/**
	 * Fetch a single scan row.
	 *
	 * @param int $id Scan row ID.
	 * @return array|null Hydrated scan row, or null when not found.
	 */
	public function get_scan( $id ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', $this->table(), $id ),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return $this->hydrate_row( $row );
	}

	/**
	 * Fetch recent scans, most recent first, for the history listing.
	 *
	 * @param int $limit  Maximum number of rows to return.
	 * @param int $offset Row offset for pagination.
	 * @return array[] Hydrated scan rows.
	 */
	public function get_recent_scans( $limit, $offset = 0 ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i ORDER BY started_at DESC LIMIT %d OFFSET %d',
				$this->table(),
				$limit,
				$offset
			),
			ARRAY_A
		);

		if ( ! $rows ) {
			return array();
		}

		return array_map( array( $this, 'hydrate_row' ), $rows );
	}

	/**
	 * Fetch the most recently completed/attempted scan (used by the dashboard widget).
	 *
	 * @return array|null Hydrated scan row, or null when no scan has run yet.
	 */
	public function get_last_scan() {
		$scan_id = Options::get_last_scan_id();

		if ( ! $scan_id ) {
			return null;
		}

		return $this->get_scan( $scan_id );
	}

	/**
	 * Get the scans table name.
	 *
	 * @return string
	 */
	private function table() {
		global $wpdb;

		return $wpdb->prefix . 'accessi_compliance_kit_scans';
	}

	/**
	 * Cast column types and decode the JSON columns of a raw scan row.
	 *
	 * @param array $row Raw `$wpdb` row.
	 * @return array Hydrated row with `violations`/`summary` decoded arrays.
	 */
	private function hydrate_row( array $row ) {
		$row['id']           = (int) $row['id'];
		$row['triggered_by'] = (int) $row['triggered_by'];
		$row['violations']   = $this->decode_json( isset( $row['violations_json'] ) ? $row['violations_json'] : '', array() );
		$row['summary']      = $this->decode_json( isset( $row['summary_json'] ) ? $row['summary_json'] : '', array() );

		unset( $row['violations_json'], $row['summary_json'] );

		return $row;
	}

	/**
	 * Decode a stored JSON column, treating invalid JSON as absent (docs/database.md §2).
	 *
	 * @param string $json    Raw JSON string.
	 * @param array  $default Value to return when the column is empty or invalid.
	 * @return array
	 */
	private function decode_json( $json, array $default ) {
		if ( empty( $json ) ) {
			return $default;
		}

		$decoded = json_decode( $json, true );

		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			Logger::log( 'Failed to decode stored scan JSON: ' . json_last_error_msg() );

			return $default;
		}

		return $decoded;
	}
}
