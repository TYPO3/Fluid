<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperArgumentsValidatedEventInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

final class CustomValidationViewHelper extends AbstractViewHelper implements ViewHelperArgumentsValidatedEventInterface
{
    public function initializeArguments(): void
    {
        $this->registerArgument('arg1', 'string', '');
        $this->registerArgument('arg2', 'string', '');
        $this->registerArgument('arg3', 'string', '');
    }

    public function render(): string
    {
        return $this->arguments['arg1'] . '|' . $this->arguments['arg2'] . '|' . $this->arguments['arg3'];
    }

    public static function argumentsValidatedEvent(array $arguments, array $argumentDefinitions, ViewHelperInterface $viewHelper): void
    {
        if (!isset($arguments['arg1']) && (!isset($arguments['arg2']) || !isset($arguments['arg3']))) {
            throw new \InvalidArgumentException('ViewHelper must either be called with arg1 or with both arg2 and arg3.', 1755274666);
        }
    }
}
