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
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * This ViewHelper cycles through the specified values.
 * This can be often used to specify CSS classes for example.
 * **Note:** To achieve the "zebra class" effect in a loop you can also use the "iteration" argument of the **for** ViewHelper.
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo"><f:cycle values="{0: 'foo', 1: 'bar', 2: 'baz'}" as="cycle">{cycle}</f:cycle></f:for>
 * </code>
 * <output>
 * foobarbazfoo
 * </output>
 *
 * <code title="Alternating CSS class">
 * <ul>
 *   <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo">
 *     <f:cycle values="{0: 'odd', 1: 'even'}" as="zebraClass">
 *       <li class="{zebraClass}">{foo}</li>
 *     </f:cycle>
 *   </f:for>
 * </ul>
 * </code>
 * <output>
 * <ul>
 *   <li class="odd">1</li>
 *   <li class="even">2</li>
 *   <li class="odd">3</li>
 *   <li class="even">4</li>
 * </ul>
 * </output>
 *
 * Note: The above examples could also be achieved using the "iteration" argument of the ForViewHelper
 */
class CycleViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('values', 'array', 'The array or object implementing \ArrayAccess (for example \SplObjectStorage) to iterated over');
        $this->registerArgument('as', 'strong', 'The name of the iteration variable', true);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy();
        $values = $arguments['values'];
        $as = $arguments['as'];
        if ($values === null) {
            return $this->evaluateChildren($renderingContext);
        }
        $values = static::initializeValues($values);
        $index = static::initializeIndex($as, $renderingContext->getViewHelperVariableContainer());

        $currentValue = isset($values[$index]) ? $values[$index] : null;

        $renderingContext->getVariableProvider()->add($as, $currentValue);
        $output = $this->evaluateChildren($renderingContext);
        $renderingContext->getVariableProvider()->remove($as);

        $index++;
        if (!isset($values[$index])) {
            $index = 0;
        }
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(static::class, $as, $index);

        return $output;
    }

    /**
     * @param mixed $values
     * @return array
     * @throws Exception
     */
    protected static function initializeValues($values): array
    {
        if (is_array($values)) {
            return array_values($values);
        }

        if (is_object($values) && $values instanceof \Traversable) {
            return iterator_to_array($values, false);
        }

        throw new Exception('CycleViewHelper only supports arrays and objects implementing \Traversable interface', 1248728393);
    }

    /**
     * @param string $as
     * @param ViewHelperVariableContainer $viewHelperVariableContainer
     * @return integer
     */
    protected static function initializeIndex(string $as, ViewHelperVariableContainer $viewHelperVariableContainer): int
    {
        $index = 0;
        if ($viewHelperVariableContainer->exists(static::class, $as)) {
            $index = $viewHelperVariableContainer->get(static::class, $as);
        }

        return $index;
    }
}
