<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * The MapViewHelper
 *
 * Example
 * ========

 * ::
 *
 *    <f:map />
 *
 * .. code-block:: text
 *
 *    output
 */
final class MapViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', '', false);
        $this->registerArgument('callback', 'string', '', true);
        $this->registerArgument('arguments', 'array', '', false, []);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $value = $arguments['value'] ?? $renderChildrenClosure();
        $callback = $arguments['callback'];
        $callbackArguments = $arguments['arguments'];

        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1712224011
            );
        }

        $value = self::iteratorToArray($value);

        if (str_contains($callback, ':')) {
            $resolver = $renderingContext->getViewHelperResolver();
            [$namespace, $shortName] = explode(':', $callback, 2);

            if (!$resolver->isNamespaceValid($namespace)) {
                throw new \InvalidArgumentException(
                    'Invalid fluid namespace "' . $namespace . '" used for callback in view helper "' . static::class . '".',
                    1712224012
                );
            }
            $viewHelper = $resolver->createViewHelperInstance($namespace, $shortName);

            $callback = fn(mixed $value): mixed => self::invokeViewHelperWithArgumentValidation($renderingContext, $viewHelper, $callbackArguments, $value);
        } else {
            if (!is_callable($callback)) {
                throw new \InvalidArgumentException(
                    'The argument "callback" is not a valid callback function in view helper "' . static::class . '".',
                    1712224013
                );
            }

            if ($callbackArguments !== []) {
                $callback = fn(mixed $value): mixed => $callback($value, ...$callbackArguments);
            }
        }

        return array_map($callback, $value);
    }

    private static function invokeViewHelperWithArgumentValidation(
        RenderingContextInterface $renderingContext,
        ViewHelperInterface $viewHelper,
        array $arguments,
        mixed $value
    ): mixed {
        $viewHelper->setArguments($arguments);

        $argumentDefinitions = $viewHelper->prepareArguments();
        foreach ($argumentDefinitions as $definition) {
            if ($definition->isRequired() && !isset($arguments[$definition->getName()])) {
                throw new \InvalidArgumentException(
                    'While using ' . $viewHelper::class . ' as callback, the required argument "' .
                    $definition->getName() . '" was not specified in view helper "' . static::class . '".',
                    1712224014
                );
            }
        }
        $viewHelper->validateArguments();

        return $renderingContext->getViewHelperInvoker()->invoke(
            $viewHelper,
            $arguments,
            $renderingContext,
            fn(): mixed => $value
        );
    }

    /**
     * This ensures compatibility with PHP 8.1
     */
    private static function iteratorToArray(\Traversable|array $iterator): array
    {
        return is_array($iterator) ? $iterator : iterator_to_array($iterator);
    }
}
