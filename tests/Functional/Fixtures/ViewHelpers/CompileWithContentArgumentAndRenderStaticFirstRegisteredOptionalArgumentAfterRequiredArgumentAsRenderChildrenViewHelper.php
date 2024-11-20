<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

final class CompileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildrenViewHelper extends AbstractViewHelper
{
    // ViewHelper tests this trait functionalities.
    use CompileWithContentArgumentAndRenderStatic;

    // set to false because of json response, not test relevant
    protected $escapeOutput = false;
    // set to false because of json response, not test relevant
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('requiredArgument', 'string', 'demo required argument before optional argument', true);
        $this->registerArgument('firstOptionalArgument', 'string', 'First optional argument which is used as render children.');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        return json_encode(
            [
                'arguments[firstOptionalArgument]' => $arguments['firstOptionalArgument'],
                'renderChildrenClosure' => $renderChildrenClosure(),
            ],
            JSON_PRETTY_PRINT,
        );
    }
}
