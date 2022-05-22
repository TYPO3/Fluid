<?php

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class CompileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildrenViewHelper
 */
class CompileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildrenViewHelper extends AbstractViewHelper
{
    // ViewHelper tests this trait functionalities.
    use CompileWithContentArgumentAndRenderStatic;

    // set to false because of json response, not test relevant
    protected $escapeOutput = false;
    // set to false because of json response, not test relevant
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('firstOptionalArgument', 'string', 'First optional argument which is used as render children.');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        return json_encode(
            [
                'arguments[firstOptionalArgument]' => $arguments['firstOptionalArgument'],
                'renderChildrenClosure' => $renderChildrenClosure(),
            ],
            JSON_PRETTY_PRINT
        );
    }
}
