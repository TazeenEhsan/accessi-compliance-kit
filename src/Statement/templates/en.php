<?php
/**
 * EAA-compliant accessibility statement template (English).
 *
 * @package AccessiComplianceKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the English accessibility statement page content.
 *
 * @param array $data {
 *     Template substitution values.
 *
 *     @type string $site_name        Site name.
 *     @type string $contact_email    Contact email for accessibility issues.
 *     @type string $compliance_level Compliance level claimed (e.g. "WCAG 2.1 Level AA").
 *     @type string $review_date      Date this statement was last reviewed, site date format.
 * }
 * @return string HTML content for the statement page (escaped, safe for `post_content`).
 */
function accessi_compliance_kit_statement_template_en( array $data ) {
	$site_name        = isset( $data['site_name'] ) ? $data['site_name'] : '';
	$contact_email    = isset( $data['contact_email'] ) ? $data['contact_email'] : '';
	$compliance_level = isset( $data['compliance_level'] ) ? $data['compliance_level'] : '';
	$review_date      = isset( $data['review_date'] ) ? $data['review_date'] : '';

	$sections = array(
		array(
			'body' => sprintf(
				/* translators: %s: site name. */
				esc_html__( '%s is committed to ensuring digital accessibility for people of all abilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.', 'accessi-compliance-kit' ),
				esc_html( $site_name )
			),
		),
		array(
			'heading' => esc_html__( 'Conformance status', 'accessi-compliance-kit' ),
			'body'    => sprintf(
				/* translators: %s: claimed WCAG conformance level, e.g. "WCAG 2.1 Level AA". */
				esc_html__( 'This website aims to conform to %s. We have not yet completed a full audit, so this statement reflects our ongoing effort rather than a certified result.', 'accessi-compliance-kit' ),
				esc_html( $compliance_level )
			),
		),
		array(
			'heading' => esc_html__( 'Known limitations', 'accessi-compliance-kit' ),
			'body'    => esc_html__( 'Despite our best efforts, some content or functionality on this site may not yet be fully accessible. We are actively working to identify and address these issues. If you encounter a barrier, please let us know using the contact details below.', 'accessi-compliance-kit' ),
		),
		array(
			'heading' => esc_html__( 'Feedback and contact information', 'accessi-compliance-kit' ),
			'body'    => sprintf(
				/* translators: %s: contact email address for accessibility issues. */
				esc_html__( 'We welcome your feedback on the accessibility of this website. Please contact us at %s if you encounter an accessibility barrier.', 'accessi-compliance-kit' ),
				esc_html( $contact_email )
			),
		),
		array(
			'heading' => esc_html__( 'Date of last review', 'accessi-compliance-kit' ),
			'body'    => esc_html( $review_date ),
		),
	);

	$html = '';

	foreach ( $sections as $section ) {
		if ( ! empty( $section['heading'] ) ) {
			$html .= '<h2>' . $section['heading'] . "</h2>\n";
		}

		$html .= '<p>' . $section['body'] . "</p>\n";
	}

	return $html;
}
