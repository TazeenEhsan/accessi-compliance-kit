/**
 * Settings tab: one `ToggleControl` per fix (all default OFF) plus the email
 * reminder opt-in, saved via AJAX (docs/admin.md §6, proposal §9).
 */
import { useState } from '@wordpress/element';
import { Button, Card, CardBody, Notice, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ajaxRequest } from './utils/ajax';

const CONTEXT_LABELS = {
	product: __( 'Product', 'accessibility-compliance-kit-for-woocommerce' ),
	checkout: __( 'Checkout', 'accessibility-compliance-kit-for-woocommerce' ),
	cart: __( 'Cart', 'accessibility-compliance-kit-for-woocommerce' ),
	global: __( 'Site-wide', 'accessibility-compliance-kit-for-woocommerce' ),
};

/**
 * @param {Object} props          Component props.
 * @param {Object} props.settings Localized `accessibilityComplianceKitForWooCommerceAdmin` data.
 * @return {JSX.Element}
 */
export default function Settings( { settings } ) {
	const fixes = settings.fixes || [];

	const [ activeFixes, setActiveFixes ] = useState( settings.activeFixes || {} );
	const [ emailOptIn, setEmailOptIn ] = useState(
		!! ( settings.settings && settings.settings.email_reminder_opt_in )
	);
	const [ status, setStatus ] = useState( 'idle' );
	const [ message, setMessage ] = useState( '' );

	const toggleFix = ( id, value ) => {
		setActiveFixes( ( current ) => ( { ...current, [ id ]: value } ) );
	};

	const save = async () => {
		setStatus( 'saving' );
		setMessage( '' );

		try {
			await ajaxRequest( settings.ajaxUrl, 'accessibility_compliance_kit_for_woocommerce_save_settings', settings.nonces.saveSettings, {
				active_fixes: JSON.stringify( activeFixes ),
				email_reminder_opt_in: emailOptIn ? '1' : '0',
			} );

			setStatus( 'success' );
		} catch ( error ) {
			setStatus( 'error' );
			setMessage( error.message );
		}
	};

	return (
		<div className="accessibility-compliance-kit-for-woocommerce-settings">
			{ fixes.map( ( fix ) => (
				<Card key={ fix.id } className="accessibility-compliance-kit-for-woocommerce-fix-card">
					<CardBody>
						<ToggleControl
							label={ fix.label }
							help={ fix.description }
							checked={ !! activeFixes[ fix.id ] }
							onChange={ ( value ) => toggleFix( fix.id, value ) }
						/>
						<div className="accessibility-compliance-kit-for-woocommerce-fix-contexts">
							{ ( fix.contexts || [] ).map( ( context ) => (
								<span key={ context } className="accessibility-compliance-kit-for-woocommerce-context-badge">
									{ CONTEXT_LABELS[ context ] || context }
								</span>
							) ) }
						</div>
					</CardBody>
				</Card>
			) ) }

			<Card className="accessibility-compliance-kit-for-woocommerce-fix-card">
				<CardBody>
					<ToggleControl
						label={ __( 'Weekly scan reminder email', 'accessibility-compliance-kit-for-woocommerce' ) }
						help={ __( 'Send a weekly email reminding you to run a scan.', 'accessibility-compliance-kit-for-woocommerce' ) }
						checked={ emailOptIn }
						onChange={ setEmailOptIn }
					/>
				</CardBody>
			</Card>

			<Button variant="primary" onClick={ save } isBusy={ 'saving' === status } disabled={ 'saving' === status }>
				{ __( 'Save settings', 'accessibility-compliance-kit-for-woocommerce' ) }
			</Button>

			{ 'success' === status && (
				<Notice status="success" isDismissible={ false }>
					{ __( 'Settings saved.', 'accessibility-compliance-kit-for-woocommerce' ) }
				</Notice>
			) }
			{ 'error' === status && message && (
				<Notice status="error" isDismissible={ false }>
					{ message }
				</Notice>
			) }
		</div>
	);
}
