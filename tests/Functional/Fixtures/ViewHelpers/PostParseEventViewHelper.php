<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class PostParseEventViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        return '';
    }

    public static function postParseEvent(ViewHelperNode $node, array $arguments, VariableProviderInterface $variableContainer): void
    {
        throw new \Exception('postParseEvent triggered');
    }
}
