/**
 * One accordion item. Native `<details>/<summary>` — keyboard- and
 * screen-reader-accessible out of the box, which is fitting for an
 * accessibility plugin — with React state layered on top so search and
 * “Expand all” / “Collapse all” can drive it too.
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * @param {Object}      props
 * @param {Object}      props.item      Item definition: title, body, optional open flag and contexts.
 * @param {Object|null} props.bulk      Last bulk command: { action: 'expand'|'collapse', stamp }.
 * @param {boolean}     props.forceOpen Open the item programmatically (active search match).
 * @return {JSX.Element}
 */
export default function GuideItem( { item, bulk, forceOpen } ) {
	const [ isOpen, setIsOpen ] = useState( !! item.open );

	useEffect( () => {
		if ( bulk ) {
			setIsOpen( 'expand' === bulk.action );
		}
	}, [ bulk ] );

	useEffect( () => {
		if ( forceOpen ) {
			setIsOpen( true );
		}
	}, [ forceOpen ] );

	return (
		<details
			className="tazeen-store-accessibility-kit-for-woocommerce-guide-item"
			open={ isOpen }
			onToggle={ ( event ) => setIsOpen( event.target.open ) }
		>
			<summary>{ item.title }</summary>
			<div className="tazeen-store-accessibility-kit-for-woocommerce-guide-item-body">
				{ item.body }
				{ item.contexts && item.contexts.length > 0 && (
					<div className="tazeen-store-accessibility-kit-for-woocommerce-fix-contexts">
						{ item.contexts.map( ( context ) => (
							<span key={ context } className="tazeen-store-accessibility-kit-for-woocommerce-context-badge">
								{ context }
							</span>
						) ) }
					</div>
				) }
			</div>
		</details>
	);
}
