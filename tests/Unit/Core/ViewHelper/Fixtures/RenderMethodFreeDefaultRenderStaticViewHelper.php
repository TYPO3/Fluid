<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Fixture ViewHelper that will throw an exception if
 * rendered, to test the infinite recursion prevention.
 *
 * See AbstractViewHelperTest->testCallRenderMethod* tests!
 */
class RenderMethodFreeDefaultRenderStaticViewHelper extends AbstractViewHelper implements ViewHelperInterface
{
}
