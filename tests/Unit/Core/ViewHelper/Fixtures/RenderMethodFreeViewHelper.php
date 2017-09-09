<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

class RenderMethodFreeViewHelper extends AbstractViewHelper implements ViewHelperInterface
{
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        return 'I was rendered';
    }
}
