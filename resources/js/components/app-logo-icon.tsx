import type { SVGAttributes } from 'react';

/**
 * Gar-Age logo: a garage with a roll-up door.
 */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 42" xmlns="http://www.w3.org/2000/svg">
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M20 2L38 12V40H2V12L20 2ZM9 19H31V40H9V19ZM11 21H29V24.5H11V21ZM11 27H29V30.5H11V27ZM11 33H29V36.5H11V33Z"
            />
        </svg>
    );
}
