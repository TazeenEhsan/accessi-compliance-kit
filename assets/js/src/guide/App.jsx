/**
 * User Guide app shell: header, live search, expand/collapse-all, jump-link
 * navigation, section cards, and the "Still need help?" footer.
 */
import { useMemo, useState } from '@wordpress/element';
import { Button, SearchControl } from '@wordpress/components';
import { __, sprintf, _n } from '@wordpress/i18n';
import { getSections } from './content';
import GuideSection from './GuideSection';
import Logo from './Logo';

const settings = window.accessibilityComplianceKitForWooCommerceGuide || {};

/**
 * Flatten a React node tree into its plain text, so accordion bodies are
 * searchable without storing the content twice.
 *
 * @param {*} node Any React node.
 * @return {string}
 */
const nodeToText = ( node ) => {
	if ( null === node || undefined === node || 'boolean' === typeof node ) {
		return '';
	}

	if ( 'string' === typeof node || 'number' === typeof node ) {
		return String( node );
	}

	if ( Array.isArray( node ) ) {
		return node.map( nodeToText ).join( ' ' );
	}

	if ( node.props ) {
		return nodeToText( node.props.children );
	}

	return '';
};

/**
 * Root guide component mounted into `#accessibility-compliance-kit-for-woocommerce-guide-root`.
 *
 * @return {JSX.Element}
 */
export default function App() {
	const [ query, setQuery ] = useState( '' );
	const [ bulk, setBulk ] = useState( null );

	const sections = useMemo( () => {
		return getSections( settings ).map( ( section ) => ( {
			...section,
			items: section.items.map( ( item ) => ( {
				...item,
				searchText: ( item.title + ' ' + nodeToText( item.body ) ).toLowerCase(),
			} ) ),
		} ) );
	}, [] );

	const searchTerm = query.trim().toLowerCase();

	const visibleSections = searchTerm
		? sections
				.map( ( section ) => ( {
					...section,
					items: section.items.filter( ( item ) => item.searchText.includes( searchTerm ) ),
				} ) )
				.filter( ( section ) => section.items.length > 0 )
		: sections;

	const matchCount = searchTerm
		? visibleSections.reduce( ( total, section ) => total + section.items.length, 0 )
		: 0;

	return (
		<>
			<header className="accessibility-compliance-kit-for-woocommerce-guide-header">
				<div className="accessibility-compliance-kit-for-woocommerce-guide-logo">
					<Logo size={ 28 } />
				</div>
				<div>
					<h1>{ __( 'User Guide', 'accessibility-compliance-kit-for-woocommerce' ) }</h1>
					<p className="accessibility-compliance-kit-for-woocommerce-guide-tagline">
						{ __( 'Everything you need to scan, fix, and document the accessibility of your WooCommerce store.', 'accessibility-compliance-kit-for-woocommerce' ) }
					</p>
				</div>
				<div className="accessibility-compliance-kit-for-woocommerce-guide-actions">
					<a className="button button-primary" href={ settings.dashboardUrl }>
						{ __( 'Open Dashboard', 'accessibility-compliance-kit-for-woocommerce' ) }
					</a>
					<a className="button" href={ settings.supportUrl } target="_blank" rel="noopener noreferrer">
						{ __( 'Support Forum', 'accessibility-compliance-kit-for-woocommerce' ) }
					</a>
				</div>
			</header>

			<div className="accessibility-compliance-kit-for-woocommerce-guide-toolbar">
				<SearchControl
					__nextHasNoMarginBottom
					label={ __( 'Search the guide', 'accessibility-compliance-kit-for-woocommerce' ) }
					placeholder={ __( 'Search the guide…', 'accessibility-compliance-kit-for-woocommerce' ) }
					value={ query }
					onChange={ setQuery }
					className="accessibility-compliance-kit-for-woocommerce-guide-search"
				/>
				<Button variant="secondary" onClick={ () => setBulk( { action: 'expand', stamp: Date.now() } ) }>
					{ __( 'Expand all', 'accessibility-compliance-kit-for-woocommerce' ) }
				</Button>
				<Button variant="secondary" onClick={ () => setBulk( { action: 'collapse', stamp: Date.now() } ) }>
					{ __( 'Collapse all', 'accessibility-compliance-kit-for-woocommerce' ) }
				</Button>
			</div>

			{ searchTerm ? (
				<p className="accessibility-compliance-kit-for-woocommerce-guide-search-count" role="status">
					{ sprintf(
						/* translators: %d: number of matching guide topics. */
						_n( '%d topic matches your search.', '%d topics match your search.', matchCount, 'accessibility-compliance-kit-for-woocommerce' ),
						matchCount
					) }
				</p>
			) : (
				<nav
					className="accessibility-compliance-kit-for-woocommerce-guide-nav"
					aria-label={ __( 'Guide sections', 'accessibility-compliance-kit-for-woocommerce' ) }
				>
					{ sections.map( ( section ) => (
						<a key={ section.id } href={ '#' + section.id }>
							{ section.title }
						</a>
					) ) }
				</nav>
			) }

			{ visibleSections.map( ( section ) => (
				<GuideSection key={ section.id } section={ section } bulk={ bulk } searching={ !! searchTerm } />
			) ) }

			<section className="accessibility-compliance-kit-for-woocommerce-guide-section accessibility-compliance-kit-for-woocommerce-guide-help">
				<h2>{ __( 'Still need help?', 'accessibility-compliance-kit-for-woocommerce' ) }</h2>
				<p>{ __( 'Post in the free support forum and we will take a look. Please include:', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				<ul>
					<li>{ __( 'The fix or feature involved', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
					<li>{ __( 'Your theme name and version', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
					<li>{ __( 'Your WooCommerce version', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
					<li>{ __( 'What you observed vs. what you expected', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
				</ul>
				<a
					className="button button-primary"
					href={ settings.supportUrl }
					target="_blank"
					rel="noopener noreferrer"
				>
					{ __( 'Open the Support Forum', 'accessibility-compliance-kit-for-woocommerce' ) }
				</a>
			</section>
		</>
	);
}
