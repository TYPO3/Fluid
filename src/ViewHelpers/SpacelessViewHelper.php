<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Space Removal ViewHelper
 *
 * Removes redundant spaces between HTML tags while
 * preserving the whitespace that may be inside HTML
 * tags. Trims the final result before output.
 *
 * Heavily inspired by Twig's corresponding node type.
 *
 * Usage of f:spaceless
 * ====================
 *
 * ::
 *
 *     <f:spaceless>
 *         <div>
 *             <div>
 *                 <div>text
 *
 *         text</div>
 *             </div>
 *         </div>
 *     </f:spaceless>
 *
 * Output::
 *
 *     <div><div><div>text
 *
 *     text</div></div></div>
 */
class SpacelessViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function render(): string
    {
        return trim(preg_replace('/\\>\\s+\\</', '><', (string)$this->renderChildren()));
    }
}
