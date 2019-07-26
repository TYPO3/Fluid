<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * This ViewHelper counts elements of the specified array or countable object.
 *
 * = Examples =
 *
 * <code title="Count array elements">
 * <f:count subject="{0:1, 1:2, 2:3, 3:4}" />
 * </code>
 * <output>
 * 4
 * </output>
 *
 * <code title="inline notation">
 * {objects -> f:count()}
 * </code>
 * <output>
 * 10 (depending on the number of items in {objects})
 * </output>
 *
 * @api
 */
class CountViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'array', 'Countable subject, array or \Countable');
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy();
        $countable = $arguments['subject'] ?? $this->evaluateChildren($renderingContext);
        if ($countable === null) {
            return 0;
        } elseif (!$countable instanceof \Countable && !is_array($countable)) {
            throw new Exception(
                sprintf(
                    'Subject given to f:count() is not countable (type: %s, value: %s)',
                    is_object($countable) ? get_class($countable) : gettype($countable),
                    is_object($countable) ? get_class($countable) : var_export($countable, true)
                )
            );
        }
        return count($countable);
    }
}
