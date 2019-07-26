<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A view helper for formatting values with printf. Either supply an array for
 * the arguments or a single value.
 * See http://www.php.net/manual/en/function.sprintf.php
 *
 * = Examples =
 *
 * <code title="Scientific notation">
 * <f:format.printf arguments="{number: 362525200}">%.3e</f:format.printf>
 * </code>
 * <output>
 * 3.625e+8
 * </output>
 *
 * <code title="Argument swapping">
 * <f:format.printf arguments="{0: 3, 1: 'Kasper'}">%2$s is great, TYPO%1$d too. Yes, TYPO%1$d is great and so is %2$s!</f:format.printf>
 * </code>
 * <output>
 * Kasper is great, TYPO3 too. Yes, TYPO3 is great and so is Kasper!
 * </output>
 *
 * <code title="Single argument">
 * <f:format.printf arguments="{1: 'TYPO3'}">We love %s</f:format.printf>
 * </code>
 * <output>
 * We love TYPO3
 * </output>
 *
 * <code title="Inline notation">
 * {someText -> f:format.printf(arguments: {1: 'TYPO3'})}
 * </code>
 * <output>
 * We love TYPO3
 * </output>
 *
 * @api
 */
class PrintfViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'String to format');
        $this->registerArgument('arguments', 'array', 'The arguments for vsprintf', false, []);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->getArrayCopy();
        $value = (string) ($arguments['value'] ?? $this->evaluateChildren($renderingContext));
        $formatParameters = (array) $arguments['arguments'];
        return vsprintf($value, $formatParameters);
    }
}
