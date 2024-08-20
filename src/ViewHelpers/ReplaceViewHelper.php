<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The ReplaceViewHelper replaces one or multiple strings with other
 * strings. This ViewHelper mimicks PHP's :php:`str_replace()` function.
 * However, it's also possible to provide replace pairs as associative array
 * via the "replace" argument.
 *
 *
 * Examples
 * ========
 *
 * Replace a single string
 * -----------------------
 * ::
 *
 *    <f:replace value="Hello World" search="World" replace="Fluid" />
 *
 * .. code-block:: text
 *
 *    Hello Fluid
 *
 *
 * Replace multiple strings
 * ------------------------
 * ::
 *
 *    <f:replace value="Hello World" search="{0: 'World', 1: 'Hello'}" replace="{0: 'Fluid', 1: 'Hi'}" />
 *
 * .. code-block:: text
 *
 *    Hi Fluid
 *
 *
 * Replace multiple strings using associative array
 * ------------------------------------------------
 * ::
 *
 *    <f:replace value="Hello World" replace="{'World': 'Fluid', 'Hello': 'Hi'}" />
 *
 * .. code-block:: text
 *
 *    Hi Fluid
 */
final class ReplaceViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', '');
        $this->registerArgument('search', 'mixed', '');
        $this->registerArgument('replace', 'mixed', '', true);
    }

    public function render(): string
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        $search = $this->arguments['search'];
        $replace = $this->arguments['replace'];
        if ($value === null || (!is_scalar($value) && !$value instanceof \Stringable)) {
            throw new \InvalidArgumentException('A stringable value must be provided.', 1710441987);
        }
        if ($search === null) {
            if (!is_iterable($replace)) {
                throw new \InvalidArgumentException(sprintf(
                    'Argument "replace" must be iterable to be used without "search" argument, "%s" given instead.',
                    get_debug_type($replace),
                ), 1710441988);
            }

            $replace = iterator_to_array($replace);

            $search = array_keys($replace);
            $replace = array_values($replace);
        } else {
            if (!is_iterable($search) && !is_scalar($search)) {
                throw new \InvalidArgumentException(sprintf(
                    'Argument "search" must be either iterable or scalar, "%s" given instead.',
                    get_debug_type($search),
                ), 1710441989);
            }
            if (!is_iterable($replace) && !is_scalar($replace)) {
                throw new \InvalidArgumentException(sprintf(
                    'Argument "replace" must be either iterable or scalar, "%s" given instead.',
                    get_debug_type($replace),
                ), 1710441990);
            }

            $search = is_iterable($search) ? iterator_to_array($search) : [$search];
            $replace = is_iterable($replace) ? iterator_to_array($replace) : [$replace];

            if (\count($search) !== \count($replace)) {
                throw new \InvalidArgumentException('Count of "search" and "replace" arguments must be the same.', 1710441991);
            }
        }
        return str_replace($search, $replace, (string)$value);
    }
}
