<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\StaticCacheable\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

final class CompilableViewHelper extends AbstractViewHelper
{
    // We leave this here as a test case for the deprecated feature
    use CompileWithRenderStatic;

    /**
     * @inheritdoc
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('page', 'int', 'The page');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ) {
        $page = $arguments['page'] ?? null;
        return (string)$page;
    }
}
