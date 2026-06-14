<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

final class TagMutationViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'Tag to mutate');
        $this->registerArgument('attributeValue', 'string', 'Value of the added data-second attribute', true);
    }

    public function getContentArgumentName(): string
    {
        return 'value';
    }

    public function render(): TagBuilder
    {
        $tag = $this->renderChildren();
        if (!$tag instanceof TagBuilder) {
            throw new InvalidArgumentValueException('TagMutationViewHelper expects a TagBuilder as input.', 1745483101);
        }
        $tag->addAttribute('data-second', $this->arguments['attributeValue']);
        return $tag;
    }
}
