<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * "THEN" -> only has an effect inside of "IF". See If-ViewHelper for documentation.
 *
 * @see \TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper
 * @api
 */
class ThenViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null)
    {
        return $this->evaluateChildren($renderingContext);
    }
}
