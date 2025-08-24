<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper counts elements of the specified array or countable object.
 *
 * Examples
 * ========
 *
 * Count array elements
 * --------------------
 *
 * ::
 *
 *     <f:count subject="{0:1, 1:2, 2:3, 3:4}" />
 *
 * Output::
 *
 *     4
 *
 * inline notation
 * ---------------
 *
 * ::
 *
 *     {objects -> f:count()}
 *
 * Output::
 *
 *     10 (depending on the number of items in ``{objects}``)
 *
 * @api
 */
class CountViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected ?bool $escapeChildren = false;

    /**
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'array', 'Countable subject, array or \Countable');
    }

    public function render(): int
    {
        $countable = $this->renderChildren();
        if ($countable === null) {
            return 0;
        }
        if (!$countable instanceof \Countable && !is_array($countable)) {
            throw new ViewHelper\Exception(
                sprintf(
                    'Subject given to f:count() is not countable (type: %s)',
                    is_object($countable) ? get_class($countable) : gettype($countable),
                ),
            );
        }
        return count($countable);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'subject';
    }
}
