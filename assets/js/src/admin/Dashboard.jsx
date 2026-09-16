/**
 * Dashboard tab: accessibility statement status card (docs/admin.md §7,
 * proposal §6 Phase 3). "Create statement page" button + exists/not-created
 * status, wired to the `accessibility_compliance_kit_for_woocommerce_generate_statement` AJAX action.
 */
import { useState } from '@wordpress/element';
import { Button, Card, CardBody, CardHeader, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ajaxRequest } from './utils/ajax';

/**
 * @param {Object} props          Component props.
 * @param {Object} props.settings Localized `accessibilityComplianceKitForWooCommerceAdmin` data.
 * @return {JSX.Element}
 */
export default function Dashboard( { settings } ) {
	const initialStatement = settings.statement || { pageId: 0, editLink: '' };

	const [ statement, setStatement ] = useState( initialStatement );
	const [ status, setStatus ] = useState( 'idle' );
	const [ message, setMessage ] = useState( '' );

	const generate = async ( forceNew ) => {
		setStatus( 'generating' );
		setMessage( '' );

		try {
			const data = await ajaxRequest(
				settings.ajaxUrl,
				'accessibility_compliance_kit_for_woocommerce_generate_statement',
				settings.nonces.generateStatement,
				{ force_new: forceNew ? '1' : '0' }
			);

			setStatement( { pageId: data.pageId, editLink: data.editLink } );
			setStatus( 'exists' === data.status ? 'already-exists' : 'created' );
		} catch ( error ) {
			setStatus( 'error' );
			setMessage( error.message );
		}
	};

	return (
		<div className="tazeen-store-accessibility-kit-for-woocommerce-dashboard">
			<Card className="tazeen-store-accessibility-kit-for-woocommerce-statement-card">
				<CardHeader>
					<h2>{ __( 'Accessibility statement', 'tazeen-store-accessibility-kit-for-woocommerce' ) }</h2>
				</CardHeader>
				<CardBody>
					{ statement.pageId ? (
						<>
							<p>
								{ __( 'An accessibility statement page has been created.', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
							</p>
							<Button variant="secondary" href={ statement.editLink }>
								{ __( 'Edit statement page', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
							</Button>
							{ ' ' }
							<Button
								variant="tertiary"
								onClick={ () => generate( true ) }
								isBusy={ 'generating' === status }
								disabled={ 'generating' === status }
							>
								{ __( 'Create new statement page', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
							</Button>
						</>
					) : (
						<>
							<p>
								{ __(
									'Generate a WordPress page pre-populated with an EAA-compliant accessibility statement template.',
									'tazeen-store-accessibility-kit-for-woocommerce'
								) }
							</p>
							<Button
								variant="primary"
								onClick={ () => generate( false ) }
								isBusy={ 'generating' === status }
								disabled={ 'generating' === status }
							>
								{ __( 'Create statement page', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
							</Button>
						</>
					) }

					{ 'already-exists' === status && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'A statement page already exists, so a new one was not created. Use "Create new statement page" to make a separate one anyway.',
								'tazeen-store-accessibility-kit-for-woocommerce'
							) }
						</Notice>
					) }
					{ 'created' === status && (
						<Notice status="success" isDismissible={ false }>
							{ __( 'Statement page created as a draft. Review it before publishing.', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
						</Notice>
					) }
					{ 'error' === status && message && (
						<Notice status="error" isDismissible={ false }>
							{ message }
						</Notice>
					) }
				</CardBody>
			</Card>
		</div>
	);
}
