<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

interface ViewHelperArgumentsValidatedEventInterface
{
    /**
     * Event method that is called after all ViewHelper argument validation has
     * been performed (and no exception has been thrown by the built-in validation).
     * This can be used to implement additional validation logic for ViewHelper
     * arguments, such as "either arg1 needs to be specified or arg2 AND arg3"
     * and to throw an InvalidArgumentValueException if that is not the case.
     *
     * @param array<string, mixed> $arguments
     * @param ArgumentDefinition[] $argumentDefinitions
     * @throws \InvalidArgumentException|InvalidArgumentValueException
     * @todo remove \InvalidArgumentException in Fluid 6
     */
    public static function argumentsValidatedEvent(array $arguments, array $argumentDefinitions, ViewHelperInterface $viewHelper): void;
}
