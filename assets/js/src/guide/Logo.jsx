/**
 * The plugin logo (universal-access figure) drawn in `currentColor`. Mirrors
 * the shapes in `AdminMenu::ICON_SHAPES` — keep them in sync.
 *
 * @param {Object} props
 * @param {number} props.size Icon width/height in pixels.
 * @return {JSX.Element}
 */
export default function Logo( { size = 28 } ) {
	return (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			width={ size }
			height={ size }
			aria-hidden="true"
			focusable="false"
		>
			<circle cx="12" cy="12" r="10.6" fill="none" stroke="currentColor" strokeWidth="1.6" />
			<circle cx="12" cy="6.9" r="2" fill="currentColor" />
			<path
				fill="currentColor"
				d="M17.6 9.4c-1.75.47-3.65.72-5.6.72s-3.85-.25-5.6-.72a.85.85 0 0 0-.44 1.64c1.44.39 2.94.63 4.44.71v2.06l-1.83 4.98a.85.85 0 0 0 1.6.59l1.72-4.68h.22l1.72 4.68a.85.85 0 0 0 1.6-.59l-1.83-4.98v-2.06c1.5-.08 3-.32 4.44-.71a.85.85 0 0 0-.44-1.64z"
			/>
		</svg>
	);
}
