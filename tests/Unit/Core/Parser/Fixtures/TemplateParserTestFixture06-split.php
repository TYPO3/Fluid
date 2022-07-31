<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

return [
    '{namespace foo=TYPO3Fluid\Fluid\ViewHelpers}',
    '<foo:format.nl2br>',
    '<foo:format.number decimals="1">',
    '{number}',
    '</foo:format.number>',
    '</foo:format.nl2br>',
    "\n"
];
