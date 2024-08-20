<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This is an example documentation with multiple lines
 * of text.
 *
 * Examples
 * ========
 *
 * We usually have some examples
 *
 * ::
 *    <demo:withDocumentation value="test" />
 *
 * @internal
 */
final class WithDocumentationViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'A test argument');
    }

    public function render(): string
    {
        return '';
    }
}
