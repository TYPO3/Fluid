<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Class OrViewHelperTest
 */
class OrViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider(['variable' => 'found', 'alternative' => 'alt %s marker']));
        return [
            'renders variable if found via argument' => ['found', $context, ['content' => 'found', 'alternative' => 'notfound']],
            'renders variable if found via child node' => ['found', $context, ['alternative' => 'notfound'], [new ObjectAccessorNode('variable')]],
            'renders alternate variable if not found via argument' => ['notfound', $context, ['alternative' => 'notfound']],
            'renders alternate variable if not found via child node' => ['alt %s marker', $context, ['alternative' => new ObjectAccessorNode('alternative')]],
            'renders alternate variable with sprintf if not found and provided with arguments' => ['alt string marker', $context, ['alternative' => new ObjectAccessorNode('alternative'), 'arguments' => ['string']]],
        ];
    }
}
