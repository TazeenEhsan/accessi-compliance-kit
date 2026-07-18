<?php
/**
 * Normalizes axe-core violation payloads into the shape stored in `violations_json`.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes the violations payload posted by the scanner bundle (docs/database.md §4)
 * and computes severity summary counts. The payload arrives over AJAX from the
 * browser, so every field is treated as untrusted and sanitized defensively.
 */
class ViolationParser {

	/**
	 * Valid severity buckets, exactly the four from proposal §4.1.
	 *
	 * @var string[]
	 */
	const SEVERITIES = array( 'critical', 'serious', 'moderate', 'minor' );

	/**
	 * Max length, in characters, of a stored node HTML snippet (docs/database.md §4).
	 *
	 * @var int
	 */
	const NODE_HTML_MAX_LENGTH = 500;

	/**
	 * Normalize a raw violations payload and compute its severity summary.
	 *
	 * @param array $raw_violations Raw violations array from the scanner bundle.
	 * @return array {
	 *     @type array $violations Normalized violations (docs/database.md §4).
	 *     @type array $summary    Counts by severity plus 'total'.
	 * }
	 */
	public static function parse( array $raw_violations ) {
		$summary          = array_fill_keys( self::SEVERITIES, 0 );
		$summary['total'] = 0;

		$violations = array();

		foreach ( $raw_violations as $raw_violation ) {
			$violation = self::normalize_violation( $raw_violation );

			if ( null === $violation ) {
				continue;
			}

			$violations[]                     = $violation;
			$summary[ $violation['impact'] ] += 1;
			$summary['total']                += 1;
		}

		return array(
			'violations' => $violations,
			'summary'    => $summary,
		);
	}

	/**
	 * Normalize a single raw violation entry.
	 *
	 * @param mixed $raw_violation Raw violation entry.
	 * @return array|null Normalized violation, or null when the entry lacks a rule id.
	 */
	private static function normalize_violation( $raw_violation ) {
		if ( ! is_array( $raw_violation ) || empty( $raw_violation['rule'] ) ) {
			return null;
		}

		$impact = isset( $raw_violation['impact'] ) ? $raw_violation['impact'] : '';

		if ( ! in_array( $impact, self::SEVERITIES, true ) ) {
			$impact = 'minor';
		}

		$description = isset( $raw_violation['description'] ) ? $raw_violation['description'] : '';
		$help        = isset( $raw_violation['help'] ) ? $raw_violation['help'] : '';
		$help_url    = isset( $raw_violation['help_url'] ) ? $raw_violation['help_url'] : '';
		$nodes       = isset( $raw_violation['nodes'] ) ? $raw_violation['nodes'] : array();

		return array(
			'rule'        => sanitize_text_field( $raw_violation['rule'] ),
			'impact'      => $impact,
			'description' => sanitize_text_field( $description ),
			'help'        => sanitize_text_field( $help ),
			'help_url'    => esc_url_raw( $help_url ),
			'nodes'       => self::normalize_nodes( $nodes ),
		);
	}

	/**
	 * Normalize a violation's affected nodes.
	 *
	 * @param mixed $raw_nodes Raw nodes array.
	 * @return array[] Normalized nodes.
	 */
	private static function normalize_nodes( $raw_nodes ) {
		if ( ! is_array( $raw_nodes ) ) {
			return array();
		}

		$nodes = array();

		foreach ( $raw_nodes as $raw_node ) {
			if ( ! is_array( $raw_node ) ) {
				continue;
			}

			$selector        = isset( $raw_node['selector'] ) ? $raw_node['selector'] : '';
			$html            = isset( $raw_node['html'] ) ? $raw_node['html'] : '';
			$failure_summary = isset( $raw_node['failure_summary'] ) ? $raw_node['failure_summary'] : '';

			$nodes[] = array(
				'selector'        => sanitize_text_field( $selector ),
				'html'            => self::truncate_html( $html ),
				'failure_summary' => sanitize_text_field( $failure_summary ),
			);
		}

		return $nodes;
	}

	/**
	 * Cap a node HTML snippet's length so stored rows stay a sane size.
	 *
	 * @param mixed $html Raw HTML snippet.
	 * @return string Truncated snippet.
	 */
	private static function truncate_html( $html ) {
		if ( ! is_string( $html ) ) {
			return '';
		}

		return function_exists( 'mb_substr' )
			? mb_substr( $html, 0, self::NODE_HTML_MAX_LENGTH )
			: substr( $html, 0, self::NODE_HTML_MAX_LENGTH );
	}
}
