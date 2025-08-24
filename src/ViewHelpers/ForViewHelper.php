<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Loop ViewHelper which can be used to iterate over arrays.
 * Implements what a basic PHP ``foreach()`` does.
 *
 * Examples
 * ========
 *
 * Simple Loop
 * -----------
 *
 * ::
 *
 *     <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo">{foo}</f:for>
 *
 * Output::
 *
 *     1234
 *
 * Output array key
 * ----------------
 *
 * ::
 *
 *     <ul>
 *         <f:for each="{fruit1: 'apple', fruit2: 'pear', fruit3: 'banana', fruit4: 'cherry'}"
 *             as="fruit" key="label"
 *         >
 *             <li>{label}: {fruit}</li>
 *         </f:for>
 *     </ul>
 *
 * Output::
 *
 *     <ul>
 *         <li>fruit1: apple</li>
 *         <li>fruit2: pear</li>
 *         <li>fruit3: banana</li>
 *         <li>fruit4: cherry</li>
 *     </ul>
 *
 * Iteration information
 * ---------------------
 *
 * ::
 *
 *     <ul>
 *         <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo" iteration="fooIterator">
 *             <li>Index: {fooIterator.index} Cycle: {fooIterator.cycle} Total: {fooIterator.total}{f:if(condition: fooIterator.isEven, then: ' Even')}{f:if(condition: fooIterator.isOdd, then: ' Odd')}{f:if(condition: fooIterator.isFirst, then: ' First')}{f:if(condition: fooIterator.isLast, then: ' Last')}</li>
 *         </f:for>
 *     </ul>
 *
 * Output::
 *
 *     <ul>
 *         <li>Index: 0 Cycle: 1 Total: 4 Odd First</li>
 *         <li>Index: 1 Cycle: 2 Total: 4 Even</li>
 *         <li>Index: 2 Cycle: 3 Total: 4 Odd</li>
 *         <li>Index: 3 Cycle: 4 Total: 4 Even Last</li>
 *     </ul>
 *
 * @api
 */
class ForViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('each', 'array', 'The array or \SplObjectStorage to iterated over', true);
        $this->registerArgument('as', 'string', 'The name of the iteration variable', true);
        $this->registerArgument('key', 'string', 'Variable to assign array key to');
        $this->registerArgument('reverse', 'boolean', 'If true, iterates in reverse', false, false);
        $this->registerArgument('iteration', 'string', 'The name of the variable to store iteration information (index, cycle, total, isFirst, isLast, isEven, isOdd)');
    }

    /**
     * @throws ViewHelper\Exception
     */
    public function render(): string
    {
        if (!isset($this->arguments['each'])) {
            return '';
        }
        if (is_object($this->arguments['each']) && !$this->arguments['each'] instanceof \Traversable) {
            throw new ViewHelper\Exception('ForViewHelper only supports arrays and objects implementing \Traversable interface', 1248728393);
        }
        if ($this->arguments['reverse'] === true) {
            $this->arguments['each'] = array_reverse(iterator_to_array($this->arguments['each']), true);
        }
        if (isset($this->arguments['iteration'])) {
            $iterationData = [
                'index' => 0,
                'cycle' => 1,
                'total' => count($this->arguments['each']),
            ];
        }
        $globalVariableProvider = $this->renderingContext->getVariableProvider();
        $localVariableProvider = new StandardVariableProvider();
        $this->renderingContext->setVariableProvider(new ScopedVariableProvider($globalVariableProvider, $localVariableProvider));
        $output = '';
        foreach ($this->arguments['each'] as $keyValue => $singleElement) {
            $localVariableProvider->add($this->arguments['as'], $singleElement);
            if (isset($this->arguments['key'])) {
                $localVariableProvider->add($this->arguments['key'], $keyValue);
            }
            if (isset($this->arguments['iteration'])) {
                $iterationData['isFirst'] = $iterationData['cycle'] === 1;
                $iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
                $iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
                $iterationData['isOdd'] = !$iterationData['isEven'];
                $localVariableProvider->add($this->arguments['iteration'], $iterationData);
                $iterationData['index']++;
                $iterationData['cycle']++;
            }
            $output .= $this->renderChildren();
        }
        $this->renderingContext->setVariableProvider($globalVariableProvider);
        return $output;
    }
}
