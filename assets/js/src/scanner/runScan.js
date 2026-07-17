/**
 * Runs bundled axe-core against the current document and normalizes the
 * results into the shape ViolationParser/ScanStorage expect (docs/database.md §4).
 */
import axe from 'axe-core';

const NODE_HTML_MAX_LENGTH = 500;

/**
 * Truncate a node's outer HTML so stored snippets stay a sane size.
 *
 * @param {string} html Raw outerHTML snippet from axe-core.
 * @return {string} Truncated snippet.
 */
function truncateHtml( html ) {
	if ( typeof html !== 'string' ) {
		return '';
	}

	return html.length > NODE_HTML_MAX_LENGTH
		? html.slice( 0, NODE_HTML_MAX_LENGTH )
		: html;
}

/**
 * Normalize a single axe-core violation into the stored shape.
 *
 * @param {Object} violation Raw axe-core violation result.
 * @return {Object} Normalized violation.
 */
function formatViolation( violation ) {
	return {
		rule: violation.id,
		impact: violation.impact,
		description: violation.description,
		help: violation.help,
		help_url: violation.helpUrl,
		nodes: ( violation.nodes || [] ).map( ( node ) => ( {
			selector: node.target.join( ' ' ),
			html: truncateHtml( node.html ),
			failure_summary: node.failureSummary || '',
		} ) ),
	};
}

/**
 * Run axe-core against the document and return normalized violations.
 *
 * @return {Promise<Array>} Resolves with the normalized violation list.
 */
export async function runScan() {
	const results = await axe.run( document );

	return results.violations.map( formatViolation );
}
