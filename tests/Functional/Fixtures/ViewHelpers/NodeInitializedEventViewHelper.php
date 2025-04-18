<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class NodeInitializedEventViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        return '';
    }

    public static function nodeInitializedEvent(ViewHelperNode $node, array $arguments, ParsingState $parsingState): void
    {
        throw new \Exception('nodeInitializedEvent triggered');
    }
}
