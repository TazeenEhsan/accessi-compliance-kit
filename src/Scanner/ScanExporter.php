<?php
/**
 * CSV export of scan history.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Utils\Capabilities;

/**
 * Streams the scan history as a CSV download through `admin-post.php`
 * (nonce + capability checked). Only the severity summary per scan is exported,
 * never the stored violation HTML. Cells that spreadsheets could interpret as
 * formulas are neutralised (OWASP "CSV injection").
 */
class ScanExporter {

	const ACTION = 'accessibility_compliance_kit_for_woocommerce_export_scans';

	const MAX_ROWS = 5000;

	const CHUNK_SIZE = 500;

	/**
	 * Scan storage service.
	 *
	 * @var ScanStorage
	 */
	private $scan_storage;

	/**
	 * Constructor.
	 *
	 * @param ScanStorage $scan_storage Scan storage service.
	 */
	public function __construct( ScanStorage $scan_storage ) {
		$this->scan_storage = $scan_storage;
	}

	/**
	 * Hook the admin-post handler. Logged-in only: no `admin_post_nopriv_*`.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_export' ) );
	}

	/**
	 * The nonce-protected download URL, localized to the History tab.
	 *
	 * @return string
	 */
	public static function download_url() {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=' . self::ACTION ),
			self::ACTION
		);
	}

	/**
	 * Send the CSV file.
	 *
	 * @return void
	 */
	public function handle_export() {
		check_admin_referer( self::ACTION );

		if ( ! Capabilities::can_scan() ) {
			wp_die(
				esc_html__( 'You are not allowed to export scans.', 'tazeen-store-accessibility-kit-for-woocommerce' ),
				'',
				array( 'response' => 403 )
			);
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( 'accessibility-scans-' . gmdate( 'Y-m-d' ) . '.csv' ) . '"' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV download, not HTML; every cell is quoted and formula-neutralised in build_csv().
		echo self::build_csv( $this->collect_rows() );
		exit;
	}

	/**
	 * Build the CSV text (with a UTF-8 BOM so spreadsheets detect the encoding).
	 *
	 * @param array[] $scans Hydrated scan rows (see ScanStorage::get_scans_for_export()).
	 * @return string
	 */
	public static function build_csv( array $scans ) {
		$lines = array(
			self::csv_line(
				array(
					'Scan ID',
					'Started (site time)',
					'Completed (site time)',
					'URL',
					'Type',
					'Status',
					'Critical',
					'Serious',
					'Moderate',
					'Minor',
					'Triggered by (user ID)',
				)
			),
		);

		foreach ( $scans as $scan ) {
			$summary = isset( $scan['summary'] ) && is_array( $scan['summary'] ) ? $scan['summary'] : array();

			$lines[] = self::csv_line(
				array(
					(int) $scan['id'],
					self::local_date( isset( $scan['started_at'] ) ? $scan['started_at'] : '' ),
					self::local_date( isset( $scan['completed_at'] ) ? $scan['completed_at'] : '' ),
					isset( $scan['url'] ) ? $scan['url'] : '',
					isset( $scan['scan_type'] ) ? $scan['scan_type'] : '',
					isset( $scan['status'] ) ? $scan['status'] : '',
					isset( $summary['critical'] ) ? (int) $summary['critical'] : 0,
					isset( $summary['serious'] ) ? (int) $summary['serious'] : 0,
					isset( $summary['moderate'] ) ? (int) $summary['moderate'] : 0,
					isset( $summary['minor'] ) ? (int) $summary['minor'] : 0,
					isset( $scan['triggered_by'] ) ? (int) $scan['triggered_by'] : 0,
				)
			);
		}

		return "\xEF\xBB\xBF" . implode( "\r\n", $lines ) . "\r\n";
	}

	/**
	 * Neutralise a cell that a spreadsheet could execute as a formula by
	 * prefixing it with an apostrophe. Integers pass through unchanged.
	 *
	 * @param mixed $value Cell value.
	 * @return string
	 */
	public static function sanitize_cell( $value ) {
		if ( is_int( $value ) ) {
			return (string) $value;
		}

		$value = (string) $value;

		if ( '' !== $value && false !== strpos( "=+-@\t\r", $value[0] ) ) {
			return "'" . $value;
		}

		return $value;
	}

	/**
	 * Encode one CSV record: every field quoted, quotes doubled.
	 *
	 * @param array $cells Cell values.
	 * @return string
	 */
	private static function csv_line( array $cells ) {
		return implode(
			',',
			array_map(
				function ( $cell ) {
					return '"' . str_replace( '"', '""', self::sanitize_cell( $cell ) ) . '"';
				},
				$cells
			)
		);
	}

	/**
	 * Convert a stored GMT datetime to site time; empty stays empty.
	 *
	 * @param string $gmt_datetime GMT `Y-m-d H:i:s` value.
	 * @return string
	 */
	private static function local_date( $gmt_datetime ) {
		if ( empty( $gmt_datetime ) ) {
			return '';
		}

		return get_date_from_gmt( $gmt_datetime, 'Y-m-d H:i:s' );
	}

	/**
	 * Read up to MAX_ROWS scans in chunks.
	 *
	 * @return array[]
	 */
	private function collect_rows() {
		$rows = array();

		for ( $offset = 0; $offset < self::MAX_ROWS; $offset += self::CHUNK_SIZE ) {
			$chunk = $this->scan_storage->get_scans_for_export( self::CHUNK_SIZE, $offset );
			$rows  = array_merge( $rows, $chunk );

			if ( count( $chunk ) < self::CHUNK_SIZE ) {
				break;
			}
		}

		return array_slice( $rows, 0, self::MAX_ROWS );
	}
}
