/**
 * Alt text tab: lists published products whose featured image has no alt text
 * and offers one confirmed bulk action that fills only the empty ones from the
 * product title.
 */
import { useCallback, useEffect, useState } from '@wordpress/element';
import { Button, Notice, Spinner } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { ajaxRequest } from './utils/ajax';

/**
 * @param {Object} props          Component props.
 * @param {Object} props.settings Localized `accessibilityComplianceKitForWooCommerceAdmin` data.
 * @return {JSX.Element}
 */
export default function AltTextAudit( { settings } ) {
	const [ products, setProducts ] = useState( [] );
	const [ total, setTotal ] = useState( 0 );
	const [ page, setPage ] = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ isConfirming, setIsConfirming ] = useState( false );
	const [ isFilling, setIsFilling ] = useState( false );
	const [ result, setResult ] = useState( null );

	const loadPage = useCallback(
		async ( targetPage ) => {
			setIsLoading( true );
			setError( '' );

			try {
				const data = await ajaxRequest(
					settings.ajaxUrl,
					'accessibility_compliance_kit_for_woocommerce_get_alt_audit',
					settings.nonces.getAltAudit,
					{ page: targetPage }
				);

				setProducts( data.products || [] );
				setTotal( data.total || 0 );
				setPage( data.page || targetPage );
				setPerPage( data.per_page || 20 );
			} catch ( loadError ) {
				setError( loadError.message );
			} finally {
				setIsLoading( false );
			}
		},
		[ settings ]
	);

	useEffect( () => {
		loadPage( 1 );
	}, [ loadPage ] );

	const fill = async () => {
		setIsConfirming( false );
		setIsFilling( true );
		setError( '' );
		setResult( null );

		try {
			const data = await ajaxRequest(
				settings.ajaxUrl,
				'accessibility_compliance_kit_for_woocommerce_fill_alt',
				settings.nonces.fillAlt
			);

			setResult( data );
			await loadPage( 1 );
		} catch ( fillError ) {
			setError( fillError.message );
		} finally {
			setIsFilling( false );
		}
	};

	const lastPage = Math.max( 1, Math.ceil( total / perPage ) );

	return (
		<div className="tazeen-store-accessibility-kit-for-woocommerce-alt-audit">
			<p>
				{ __(
					'Published products whose featured image has no alt text. Screen readers cannot describe these images (WCAG 1.1.1). Filling only fills empty alt text and never overwrites existing text.',
					'tazeen-store-accessibility-kit-for-woocommerce'
				) }
			</p>

			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }
			{ result && (
				<Notice status="success" isDismissible={ false }>
					{ sprintf(
						/* translators: %d: number of images updated. */
						_n( 'Filled alt text on %d image.', 'Filled alt text on %d images.', result.filled, 'tazeen-store-accessibility-kit-for-woocommerce' ),
						result.filled
					) }{ ' ' }
					{ result.remaining > 0 &&
						sprintf(
							/* translators: %d: number of products still without alt text. */
							__( '%d products still need alt text; run the action again to continue.', 'tazeen-store-accessibility-kit-for-woocommerce' ),
							result.remaining
						) }
				</Notice>
			) }

			{ isLoading ? (
				<Spinner />
			) : (
				<>
					<p>
						<strong>
							{ sprintf(
								/* translators: %d: number of products missing alt text. */
								_n( '%d product is missing alt text.', '%d products are missing alt text.', total, 'tazeen-store-accessibility-kit-for-woocommerce' ),
								total
							) }
						</strong>
					</p>
					{ total > 0 && (
						<table className="tazeen-store-accessibility-kit-for-woocommerce-history-table widefat striped">
							<thead>
								<tr>
									<th>{ __( 'Image', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</th>
									<th>{ __( 'Product', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</th>
								</tr>
							</thead>
							<tbody>
								{ products.map( ( product ) => (
									<tr key={ product.id }>
										<td>
											{ product.imageUrl && (
												<img
													src={ product.imageUrl }
													alt=""
													width="48"
													height="48"
													className="tazeen-store-accessibility-kit-for-woocommerce-alt-thumb"
												/>
											) }
										</td>
										<td>
											<a href={ product.editLink }>{ product.title || __( '(no title)', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</a>
										</td>
									</tr>
								) ) }
							</tbody>
						</table>
					) }
				</>
			) }

			<div className="tazeen-store-accessibility-kit-for-woocommerce-history-pagination">
				<Button variant="secondary" disabled={ page <= 1 || isLoading } onClick={ () => loadPage( page - 1 ) }>
					{ __( 'Previous', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
				<Button variant="secondary" disabled={ page >= lastPage || isLoading } onClick={ () => loadPage( page + 1 ) }>
					{ __( 'Next', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
			</div>

			{ total > 0 && ! isConfirming && (
				<Button variant="primary" onClick={ () => setIsConfirming( true ) } isBusy={ isFilling } disabled={ isFilling || isLoading }>
					{ __( 'Fill missing alt text from product title', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
			) }
			{ isConfirming && (
				<div role="alertdialog" aria-label={ __( 'Confirm', 'tazeen-store-accessibility-kit-for-woocommerce' ) } className="tazeen-store-accessibility-kit-for-woocommerce-alt-confirm">
					<p>
						{ __(
							'This saves each product title as the alt text of its featured image, for up to 200 images at a time. Images that already have alt text are not touched. Continue?',
							'tazeen-store-accessibility-kit-for-woocommerce'
						) }
					</p>
					<Button variant="primary" onClick={ fill }>
						{ __( 'Yes, fill missing alt text', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					</Button>{ ' ' }
					<Button variant="tertiary" onClick={ () => setIsConfirming( false ) }>
						{ __( 'Cancel', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
					</Button>
				</div>
			) }
		</div>
	);
}
