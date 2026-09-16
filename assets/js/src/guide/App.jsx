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
 * Root guide component mounted into `#tazeen-store-accessibility-kit-for-woocommerce-guide-root`.
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
			<header className="tazeen-store-accessibility-kit-for-woocommerce-guide-header">
				<div className="tazeen-store-accessibility-kit-for-woocommerce-guide-logo">
					<Logo size={ 28 } />
				</div>
				<div>
					<h1>{ __( 'User Guide', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</h1>
					<p className="tazeen-store-accessibility-kit-for-woocommerce-guide-tagline">
						{ __( 'Everything you need to scan, fix, and document the accessibility of your WooCommerce store.', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					</p>
				</div>
				<div className="tazeen-store-accessibility-kit-for-woocommerce-guide-actions">
					<a className="button button-primary" href={ settings.dashboardUrl }>
						{ __( 'Open Dashboard', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					</a>
					<a className="button" href={ settings.supportUrl } target="_blank" rel="noopener noreferrer">
						{ __( 'Support Forum', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					</a>
				</div>
			</header>

			<div className="tazeen-store-accessibility-kit-for-woocommerce-guide-toolbar">
				<SearchControl
					__nextHasNoMarginBottom
					label={ __( 'Search the guide', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					placeholder={ __( 'Search the guide…', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					value={ query }
					onChange={ setQuery }
					className="tazeen-store-accessibility-kit-for-woocommerce-guide-search"
				/>
				<Button variant="secondary" onClick={ () => setBulk( { action: 'expand', stamp: Date.now() } ) }>
					{ __( 'Expand all', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
				<Button variant="secondary" onClick={ () => setBulk( { action: 'collapse', stamp: Date.now() } ) }>
					{ __( 'Collapse all', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
			</div>

			{ searchTerm ? (
				<p className="tazeen-store-accessibility-kit-for-woocommerce-guide-search-count" role="status">
					{ sprintf(
						/* translators: %d: number of matching guide topics. */
						_n( '%d topic matches your search.', '%d topics match your search.', matchCount, 'tazeen-store-accessibility-kit-for-woocommerce' ),
						matchCount
					) }
				</p>
			) : (
				<nav
					className="tazeen-store-accessibility-kit-for-woocommerce-guide-nav"
					aria-label={ __( 'Guide sections', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
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

			<section className="tazeen-store-accessibility-kit-for-woocommerce-guide-section tazeen-store-accessibility-kit-for-woocommerce-guide-help">
				<h2>{ __( 'Still need help?', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</h2>
				<p>{ __( 'Post in the free support forum and we will take a look. Please include:', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</p>
				<ul>
					<li>{ __( 'The fix or feature involved', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</li>
					<li>{ __( 'Your theme name and version', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</li>
					<li>{ __( 'Your WooCommerce version', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</li>
					<li>{ __( 'What you observed vs. what you expected', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</li>
				</ul>
				<a
					className="button button-primary"
					href={ settings.supportUrl }
					target="_blank"
					rel="noopener noreferrer"
				>
					{ __( 'Open the Support Forum', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</a>
			</section>
		</>
	);
}
