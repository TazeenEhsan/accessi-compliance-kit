/**
 * One section card: heading, optional intro, and its accordion items.
 */
import GuideItem from './GuideItem';

/**
 * @param {Object}      props
 * @param {Object}      props.section   Section definition: id, title, optional intro, items.
 * @param {Object|null} props.bulk      Last bulk expand/collapse command.
 * @param {boolean}     props.searching Whether a search query is active (matches are forced open).
 * @return {JSX.Element}
 */
export default function GuideSection( { section, bulk, searching } ) {
	return (
		<section id={ section.id } className="accessi-compliance-kit-guide-section">
			<h2>{ section.title }</h2>
			{ section.intro && <p className="accessi-compliance-kit-guide-intro">{ section.intro }</p> }
			{ section.items.map( ( item ) => (
				<GuideItem key={ item.title } item={ item } bulk={ bulk } forceOpen={ searching } />
			) ) }
		</section>
	);
}
