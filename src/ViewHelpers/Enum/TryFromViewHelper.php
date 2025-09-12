<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Enum;

use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The ViewHelper returns an enum case for the passed backed enum and value.
 *
 * Examples
 * ========
 *
 * ::
 *     <f:enum.tryFrom
 *         enum="\Vendor\Package\SomeEnum"
 *         value="42"
 *     />
 *
 * Returns::
 *
 *     \Vendor\Package\SomeEnum::TheAnswer
 *
 * If a value as argument is passed which is not defined as a case in the backed enum, null is returned.
 *
 * Using in combination with the VariableViewHelper
 * ------------------------------------------------
 *
 * The ViewHelper is best used in combination with the VariableViewHelper:
 *
 * ::
 *
 *     <f:variable name="someEnumCase" value="{f:enum.tryFrom(enum: '\Vendor\Package\SomeEnum', value: '{someFieldValue}'}"/>
 *
 * The enum case can then be used, for example:
 *
 * Get the name of the case:
 *
 * ::
 *
 *     {someEnumCase.name}
 *
 * returns
 *
 * ::
 *
 *     TheAnswer
 *
 * Get the value of the case:
 *
 * ::
 *
 *    {someEnumCase.value}
 *
 * returns
 *
 * ::
 *
 *     42
 *
 * Retrieve the return value from function "getSomething()" defined in the given backed enum:
 *
 * ::
 *
 *     {someEnumCase.something}
 */
final class TryFromViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'enum',
            'string',
            'Fully-qualified name of the backed enum',
            true,
        );

        $this->registerArgument(
            'value',
            'mixed',
            'The value to try from',
            true,
        );
    }

    public function render(): ?\BackedEnum
    {
        $enum = $this->arguments['enum'];
        $value = $this->arguments['value'];
        if (! \enum_exists($enum)) {
            throw new ViewHelper\Exception('Enum does not exist!', 1757668148);
        }

        $reflection = new \ReflectionEnum($enum);
        if (! $reflection->isBacked()) {
            throw new ViewHelper\Exception('Given enum is not a backed enum!', 1757668149);
        }

        $backingType = $reflection->getBackingType()->getName();
        $valueType = \get_debug_type($value);
        if ($valueType !== 'int' && $valueType !== 'string') {
            throw new ViewHelper\Exception('Value must be of type "int" or "string"', 1757668151);
        }
        if ($backingType !== $valueType) {
            $value = $backingType === 'int' ? (int)$value : (string)$value;
        }

        return $enum::tryFrom($value);
    }
}
