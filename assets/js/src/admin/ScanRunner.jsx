/**
 * Iframe orchestration for the single-page scan flow (proposal §5.5 steps 1–4).
 * Opens a hidden iframe at the target URL with the scan flag, waits for the
 * scanner bundle's token-gated `postMessage`, then saves the result via AJAX.
 */
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { Button, Notice, Spinner, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ajaxRequest } from './utils/ajax';

const SCAN_TIMEOUT_MS = 60000;

/**
 * Whether `url` resolves to the same host as `homeUrl`.
 *
 * @param {string} url     Candidate URL.
 * @param {string} homeUrl Site home URL.
 * @return {boolean}
 */
function isSameSite( url, homeUrl ) {
	try {
		return new URL( url, homeUrl ).host.toLowerCase() === new URL( homeUrl ).host.toLowerCase();
	} catch ( error ) {
		return false;
	}
}

/**
 * Append the scan query flag to a URL, preserving any existing query string.
 *
 * @param {string} url      Target URL.
 * @param {string} queryVar Query variable name for the scan flag.
 * @return {string}
 */
function withScanFlag( url, queryVar ) {
	try {
		const parsed = new URL( url );
		parsed.searchParams.set( queryVar, '1' );
		return parsed.toString();
	} catch ( error ) {
		return url;
	}
}

/**
 * URL input + "Scan this page" button that runs a scan in a hidden iframe.
 *
 * @param {Object}   props              Component props.
 * @param {Object}   props.settings     Localized `accessibilityComplianceKitForWooCommerceAdmin` data.
 * @param {Function} props.onScanSaved  Called with the saved scan ID on success.
 * @return {JSX.Element}
 */
export default function ScanRunner( { settings, onScanSaved } ) {
	const [ url, setUrl ] = useState( settings.prefillUrl || settings.homeUrl || '' );
	const [ status, setStatus ] = useState( 'idle' );
	const [ message, setMessage ] = useState( '' );

	const iframeRef = useRef( null );
	const timeoutRef = useRef( null );
	const listenerRef = useRef( null );

	const cleanup = useCallback( () => {
		if ( timeoutRef.current ) {
			window.clearTimeout( timeoutRef.current );
			timeoutRef.current = null;
		}

		if ( listenerRef.current ) {
			window.removeEventListener( 'message', listenerRef.current );
			listenerRef.current = null;
		}

		if ( iframeRef.current ) {
			iframeRef.current.remove();
			iframeRef.current = null;
		}
	}, [] );

	useEffect( () => cleanup, [ cleanup ] );

	const startScan = () => {
		setMessage( '' );

		if ( ! url || ! isSameSite( url, settings.homeUrl ) ) {
			setStatus( 'error' );
			setMessage( __( 'The scan URL must be on this site.', 'tazeen-store-accessibility-kit-for-woocommerce' ) );
			return;
		}

		cleanup();
		setStatus( 'running' );

		const handleMessage = async ( event ) => {
			if ( event.origin !== window.location.origin ) {
				return;
			}

			const data = event.data;

			if ( ! data || 'tazeen-store-accessibility-kit-for-woocommerce-scanner' !== data.source || data.token !== settings.scannerToken ) {
				return;
			}

			cleanup();

			if ( 'error' === data.status ) {
				setStatus( 'error' );
				setMessage( data.message || __( 'The scan could not be completed.', 'tazeen-store-accessibility-kit-for-woocommerce' ) );
				return;
			}

			try {
				const saved = await ajaxRequest( settings.ajaxUrl, 'accessibility_compliance_kit_for_woocommerce_run_scan', settings.nonces.runScan, {
					url,
					violations: JSON.stringify( data.violations || [] ),
				} );

				setStatus( 'success' );
				onScanSaved( saved.id );
			} catch ( error ) {
				setStatus( 'error' );
				setMessage( error.message );
			}
		};

		listenerRef.current = handleMessage;
		window.addEventListener( 'message', handleMessage );

		const iframe = document.createElement( 'iframe' );
		iframe.setAttribute( 'aria-hidden', 'true' );
		iframe.setAttribute( 'tabindex', '-1' );
		iframe.style.position = 'absolute';
		iframe.style.width = '1px';
		iframe.style.height = '1px';
		iframe.style.opacity = '0';
		iframe.style.pointerEvents = 'none';
		iframe.addEventListener( 'error', () => {
			cleanup();
			setStatus( 'error' );
			setMessage( __( 'The page could not be loaded for scanning.', 'tazeen-store-accessibility-kit-for-woocommerce' ) );
		} );
		iframe.src = withScanFlag( url, settings.scanQueryVar || 'accessibility_compliance_kit_for_woocommerce_scan' );

		iframeRef.current = iframe;
		document.body.appendChild( iframe );

		timeoutRef.current = window.setTimeout( () => {
			cleanup();
			setStatus( 'error' );
			setMessage( __( 'The scan timed out. Please try again.', 'tazeen-store-accessibility-kit-for-woocommerce' ) );
		}, SCAN_TIMEOUT_MS );
	};

	const isRunning = 'running' === status;

	return (
		<div className="tazeen-store-accessibility-kit-for-woocommerce-scan-runner">
			<TextControl
				label={ __( 'Page URL to scan', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				help={ __( 'Must be a URL on this site.', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				value={ url }
				onChange={ setUrl }
				disabled={ isRunning }
			/>
			<Button variant="primary" onClick={ startScan } disabled={ isRunning || ! url } isBusy={ isRunning }>
				{ isRunning
					? __( 'Scanning…', 'tazeen-store-accessibility-kit-for-woocommerce' )
					: __( 'Scan this page', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
			</Button>
			{ isRunning && <Spinner /> }
			{ 'error' === status && message && (
				<Notice status="error" isDismissible={ false }>
					{ message }
				</Notice>
			) }
			{ 'success' === status && (
				<Notice status="success" isDismissible={ false }>
					{ __( 'Scan complete. Detected issues are shown below.', 'tazeen-store-accessibility-kit-for-woocommerce' ) }
				</Notice>
			) }
		</div>
	);
}
