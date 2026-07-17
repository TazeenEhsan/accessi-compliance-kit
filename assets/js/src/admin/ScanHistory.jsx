/**
 * History tab: paginated list of past scans with click-through to a scan's
 * stored results (docs/admin.md §5).
 */
import { useCallback, useEffect, useState } from '@wordpress/element';
import { Button, Notice, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ajaxRequest } from './utils/ajax';
import ScanResults from './ScanResults';

/**
 * @param {Object} props            Component props.
 * @param {Object} props.settings   Localized `accessiComplianceKitAdmin` data.
 * @return {JSX.Element}
 */
export default function ScanHistory( { settings } ) {
	const [ scans, setScans ] = useState( [] );
	const [ page, setPage ] = useState( 1 );
	const [ hasMore, setHasMore ] = useState( false );
	const [ isLoadingList, setIsLoadingList ] = useState( false );
	const [ listError, setListError ] = useState( '' );

	const [ selectedScan, setSelectedScan ] = useState( null );
	const [ isLoadingScan, setIsLoadingScan ] = useState( false );
	const [ scanError, setScanError ] = useState( '' );

	const loadPage = useCallback(
		async ( targetPage ) => {
			setIsLoadingList( true );
			setListError( '' );

			try {
				const data = await ajaxRequest(
					settings.ajaxUrl,
					'accessi_compliance_kit_get_scans',
					settings.nonces.getScans,
					{ page: targetPage }
				);

				setScans( data.scans || [] );
				setHasMore( !! data.has_more );
				setPage( data.page || targetPage );
			} catch ( error ) {
				setListError( error.message );
			} finally {
				setIsLoadingList( false );
			}
		},
		[ settings ]
	);

	useEffect( () => {
		loadPage( 1 );
	}, [ loadPage ] );

	const selectScan = async ( id ) => {
		setIsLoadingScan( true );
		setScanError( '' );
		setSelectedScan( null );

		try {
			const data = await ajaxRequest( settings.ajaxUrl, 'accessi_compliance_kit_get_scan', settings.nonces.getScan, {
				id,
			} );

			setSelectedScan( data );
		} catch ( error ) {
			setScanError( error.message );
		} finally {
			setIsLoadingScan( false );
		}
	};

	return (
		<div className="accessi-compliance-kit-scan-history">
			{ listError && (
				<Notice status="error" isDismissible={ false }>
					{ listError }
				</Notice>
			) }
			{ isLoadingList ? (
				<Spinner />
			) : (
				<table className="accessi-compliance-kit-history-table widefat striped">
					<thead>
						<tr>
							<th>{ __( 'Date', 'accessi-compliance-kit' ) }</th>
							<th>{ __( 'URL', 'accessi-compliance-kit' ) }</th>
							<th>{ __( 'Status', 'accessi-compliance-kit' ) }</th>
							<th>{ __( 'Critical', 'accessi-compliance-kit' ) }</th>
							<th>{ __( 'Serious', 'accessi-compliance-kit' ) }</th>
							<th>{ __( 'Moderate', 'accessi-compliance-kit' ) }</th>
							<th>{ __( 'Minor', 'accessi-compliance-kit' ) }</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{ 0 === scans.length && (
							<tr>
								<td colSpan={ 8 }>{ __( 'No scans yet.', 'accessi-compliance-kit' ) }</td>
							</tr>
						) }
						{ scans.map( ( scan ) => (
							<tr key={ scan.id }>
								<td>{ scan.started_at }</td>
								<td>{ scan.url }</td>
								<td>{ scan.status }</td>
								<td>{ ( scan.summary && scan.summary.critical ) || 0 }</td>
								<td>{ ( scan.summary && scan.summary.serious ) || 0 }</td>
								<td>{ ( scan.summary && scan.summary.moderate ) || 0 }</td>
								<td>{ ( scan.summary && scan.summary.minor ) || 0 }</td>
								<td>
									<Button variant="link" onClick={ () => selectScan( scan.id ) }>
										{ __( 'View results', 'accessi-compliance-kit' ) }
									</Button>
								</td>
							</tr>
						) ) }
					</tbody>
				</table>
			) }
			<div className="accessi-compliance-kit-history-pagination">
				<Button variant="secondary" disabled={ page <= 1 || isLoadingList } onClick={ () => loadPage( page - 1 ) }>
					{ __( 'Previous', 'accessi-compliance-kit' ) }
				</Button>
				<Button variant="secondary" disabled={ ! hasMore || isLoadingList } onClick={ () => loadPage( page + 1 ) }>
					{ __( 'Next', 'accessi-compliance-kit' ) }
				</Button>
			</div>
			{ scanError && (
				<Notice status="error" isDismissible={ false }>
					{ scanError }
				</Notice>
			) }
			{ isLoadingScan && <Spinner /> }
			<ScanResults scan={ selectedScan } severityLabels={ settings.severityLabels } />
		</div>
	);
}
