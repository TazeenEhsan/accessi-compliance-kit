/**
 * Renders a stored scan's violations grouped by severity, worded as
 * "detected issues" per proposal §9. Never uses `dangerouslySetInnerHTML`
 * with scan data (docs/security.md §3) — snippets render as escaped text.
 */
import { Card, CardBody, ExternalLink, PanelBody } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

const SEVERITY_ORDER = [ 'critical', 'serious', 'moderate', 'minor' ];

/**
 * A single expandable violation row.
 *
 * @param {Object} props           Component props.
 * @param {Object} props.violation Normalized violation (docs/database.md §4).
 * @return {JSX.Element}
 */
function ViolationPanel( { violation } ) {
	return (
		<PanelBody title={ violation.rule } initialOpen={ false }>
			<p>{ violation.description }</p>
			<p>
				<strong>{ __( 'How to fix:', 'accessi-compliance-kit' ) }</strong> { violation.help }
			</p>
			{ violation.help_url && (
				<p>
					<ExternalLink href={ violation.help_url }>
						{ __( 'Learn more', 'accessi-compliance-kit' ) }
					</ExternalLink>
				</p>
			) }
			{ ( violation.nodes || [] ).map( ( node, index ) => (
				<div className="accessi-compliance-kit-violation-node" key={ index }>
					<p>
						<code>{ node.selector }</code>
					</p>
					{ node.failure_summary && <p>{ node.failure_summary }</p> }
					{ node.html && (
						<pre>
							<code>{ node.html }</code>
						</pre>
					) }
				</div>
			) ) }
		</PanelBody>
	);
}

/**
 * One severity group heading plus its violation panels.
 *
 * @param {Object} props          Component props.
 * @param {string} props.severity Severity key (critical|serious|moderate|minor).
 * @param {string} props.label    Translated severity label.
 * @param {Array}  props.items    Violations in this severity bucket.
 * @return {JSX.Element}
 */
function SeverityGroup( { severity, label, items } ) {
	return (
		<div className={ `accessi-compliance-kit-severity-group accessi-compliance-kit-severity-${ severity }` }>
			<h3>{ `${ label } (${ items.length })` }</h3>
			{ items.map( ( violation, index ) => (
				<ViolationPanel key={ `${ violation.rule }-${ index }` } violation={ violation } />
			) ) }
		</div>
	);
}

/**
 * Grouped, expandable results view for a single stored scan.
 *
 * @param {Object} props                Component props.
 * @param {Object} props.scan           Hydrated scan row (id, url, violations, summary).
 * @param {Object} [props.severityLabels] Map of severity key to translated label.
 * @return {JSX.Element|null}
 */
export default function ScanResults( { scan, severityLabels = {} } ) {
	if ( ! scan ) {
		return null;
	}

	const violations = scan.violations || [];
	const summary = scan.summary || {};

	const bySeverity = SEVERITY_ORDER.reduce( ( acc, severity ) => {
		acc[ severity ] = violations.filter( ( violation ) => violation.impact === severity );
		return acc;
	}, {} );

	const uncategorized = violations.filter(
		( violation ) => ! SEVERITY_ORDER.includes( violation.impact )
	);

	return (
		<Card className="accessi-compliance-kit-scan-results">
			<CardBody>
				{ 0 === violations.length ? (
					<p>{ __( 'No detected issues on this page.', 'accessi-compliance-kit' ) }</p>
				) : (
					<p>
						{ sprintf(
							/* translators: 1: number of detected issues, 2: scanned URL */
							__( '%1$d detected issues found on %2$s', 'accessi-compliance-kit' ),
							summary.total || violations.length,
							scan.url
						) }
					</p>
				) }
				{ SEVERITY_ORDER.map(
					( severity ) =>
						bySeverity[ severity ].length > 0 && (
							<SeverityGroup
								key={ severity }
								severity={ severity }
								label={ severityLabels[ severity ] || severity }
								items={ bySeverity[ severity ] }
							/>
						)
				) }
				{ uncategorized.length > 0 && (
					<SeverityGroup
						severity="unknown"
						label={ __( 'Other', 'accessi-compliance-kit' ) }
						items={ uncategorized }
					/>
				) }
			</CardBody>
		</Card>
	);
}
