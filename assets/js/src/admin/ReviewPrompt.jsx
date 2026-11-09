/**
 * One dismissible review prompt, shown on the plugin page only after three
 * completed scans. "Maybe later" snoozes it for 30 days, "Already did" and
 * following the review link silence it for good. Never blocks the UI.
 */
import { useState } from '@wordpress/element';
import { Button, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ajaxRequest } from './utils/ajax';

/**
 * @param {Object} props          Component props.
 * @param {Object} props.settings Localized `accessibilityComplianceKitForWooCommerceAdmin` data.
 * @return {JSX.Element|null}
 */
export default function ReviewPrompt( { settings } ) {
	const prompt = settings.reviewPrompt || {};
	const [ visible, setVisible ] = useState( !! prompt.show );

	if ( ! visible ) {
		return null;
	}

	// Fire-and-forget: the prompt closes immediately; a failed save only means
	// it may appear again next visit.
	const answer = ( choice ) => {
		setVisible( false );
		ajaxRequest(
			settings.ajaxUrl,
			'accessibility_compliance_kit_for_woocommerce_review_prompt',
			settings.nonces.reviewPrompt,
			{ choice }
		).catch( () => {} );
	};

	return (
		<Notice
			className="tazeen-store-accessibility-kit-for-woocommerce-review-prompt"
			status="info"
			onRemove={ () => answer( 'later' ) }
		>
			<p>
				{ __(
					'Enjoying Store Accessibility Kit? A short review on WordPress.org helps other store owners find it.',
					'tazeen-store-accessibility-kit-for-woocommerce'
				) }
			</p>
			<div className="tazeen-store-accessibility-kit-for-woocommerce-review-actions">
				<Button
					variant="primary"
					href={ prompt.reviewUrl }
					target="_blank"
					rel="noopener noreferrer"
					onClick={ () => answer( 'done' ) }
				>
					{ __( 'Leave a review', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
				<Button variant="tertiary" onClick={ () => answer( 'later' ) }>
					{ __( 'Maybe later', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
				<Button variant="tertiary" onClick={ () => answer( 'done' ) }>
					{ __( 'Already did', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Button>
			</div>
		</Notice>
	);
}
