/**
 * Admin app shell: tab layout (Scan | History | Settings) using
 * `@wordpress/components` (docs/admin.md §3).
 */
import { useCallback, useState } from '@wordpress/element';
import { Button, Notice, Spinner, TabPanel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ScanRunner from './ScanRunner';
import ScanResults from './ScanResults';
import ScanHistory from './ScanHistory';
import Settings from './Settings';
import Dashboard from './Dashboard';
import { ajaxRequest } from './utils/ajax';

const settings = window.accessibilityComplianceKitForWooCommerceAdmin || {};

const TABS = [
	{ name: 'dashboard', title: __( 'Dashboard', 'tazeen-store-accessibility-kit-for-woocommerce' ), className: 'tazeen-store-accessibility-kit-for-woocommerce-tab-dashboard' },
	{ name: 'scan', title: __( 'Scan', 'tazeen-store-accessibility-kit-for-woocommerce' ), className: 'tazeen-store-accessibility-kit-for-woocommerce-tab-scan' },
	{ name: 'history', title: __( 'History', 'tazeen-store-accessibility-kit-for-woocommerce' ), className: 'tazeen-store-accessibility-kit-for-woocommerce-tab-history' },
	{ name: 'settings', title: __( 'Settings', 'tazeen-store-accessibility-kit-for-woocommerce' ), className: 'tazeen-store-accessibility-kit-for-woocommerce-tab-settings' },
];

/**
 * Root admin component mounted into `#tazeen-store-accessibility-kit-for-woocommerce-admin`.
 *
 * @return {JSX.Element}
 */
export default function App() {
	const [ scan, setScan ] = useState( null );
	const [ isLoadingScan, setIsLoadingScan ] = useState( false );
	const [ loadError, setLoadError ] = useState( '' );

	const loadScan = useCallback( async ( scanId ) => {
		setIsLoadingScan( true );
		setLoadError( '' );

		try {
			const data = await ajaxRequest( settings.ajaxUrl, 'accessibility_compliance_kit_for_woocommerce_get_scan', settings.nonces.getScan, {
				id: scanId,
			} );

			setScan( data );
		} catch ( error ) {
			setLoadError( error.message );
		} finally {
			setIsLoadingScan( false );
		}
	}, [] );

	return (
		<div className="tazeen-store-accessibility-kit-for-woocommerce-admin-app">
			<div className="tazeen-store-accessibility-kit-for-woocommerce-admin-header">
				<h1>{ __( 'Accessibility', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</h1>
				{ settings.guideUrl && (
					<Button
						variant="secondary"
						href={ settings.guideUrl }
						icon="book-alt"
						className="tazeen-store-accessibility-kit-for-woocommerce-guide-button"
					>
						{ __( 'User Guide', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					</Button>
				) }
			</div>
			<TabPanel tabs={ TABS }>
				{ ( tab ) => {
					if ( 'dashboard' === tab.name ) {
						return <Dashboard settings={ settings } />;
					}

					if ( 'history' === tab.name ) {
						return <ScanHistory settings={ settings } />;
					}

					if ( 'settings' === tab.name ) {
						return <Settings settings={ settings } />;
					}

					return (
						<div>
							<ScanRunner settings={ settings } onScanSaved={ loadScan } />
							{ loadError && (
								<Notice status="error" isDismissible={ false }>
									{ loadError }
								</Notice>
							) }
							{ isLoadingScan && <Spinner /> }
							<ScanResults scan={ scan } severityLabels={ settings.severityLabels } />
						</div>
					);
				} }
			</TabPanel>
		</div>
	);
}
