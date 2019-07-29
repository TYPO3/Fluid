<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Class InlineViewHelperTest
 */
class InlineViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider(['variable' => 'rendered']));
        return [
            'executes sub-fluid code' => ['rendered', $context, ['code' => '{variable}']],
        ];
    }
}
