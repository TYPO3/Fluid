<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\StaticCacheable\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class CompilableViewHelper extends AbstractViewHelper
{
    /**
     * @inheritdoc
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('page', 'int', 'The page');
    }

    public function render(): string
    {
        $page = $this->arguments['page'] ?? null;
        return (string)$page;
    }
}
