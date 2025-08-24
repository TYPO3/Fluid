<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Wrapper for PHPs :php:`nl2br` function.
 * See https://www.php.net/manual/function.nl2br.php.
 *
 * Examples
 * ========
 *
 * Default
 * -------
 *
 * ::
 *
 *    <f:format.nl2br>{text_with_linebreaks}</f:format.nl2br>
 *
 * Text with line breaks replaced by ``<br />``
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *    {text_with_linebreaks -> f:format.nl2br()}
 *
 * Text with line breaks replaced by ``<br />``
 */
final class Nl2brViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'string to format');
    }

    public function render(): string
    {
        return nl2br((string)$this->renderChildren());
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }
}
