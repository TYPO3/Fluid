<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Rendering;

use TYPO3Fluid\Fluid\Core\Parser\Exception;

/**
 * Shared string conversion for syntax trees and compiled templates.
 *
 * @internal
 */
final class StringConverter
{
    public static function castToString(
        mixed $value,
        ?RenderingContextInterface $renderingContext = null,
        ?string $originalTemplatePath = null,
    ): string {
        if (is_object($value) && !$value instanceof \Stringable) {
            $message = 'Cannot cast object of type "' . get_class($value) . '" to string.';
            $code = 1273753083;
        } elseif (is_array($value)) {
            $message = 'Cannot cast an array to string.';
            $code = 1698750868;
        } else {
            // Exceptions from application __toString() methods must propagate unchanged.
            return (string)$value;
        }
        // @todo decide if this method should be added to RenderingContextInterface in Fluid 6
        if ($originalTemplatePath === null && $renderingContext !== null && method_exists($renderingContext, 'getOriginalTemplatePath')) {
            $originalTemplatePath = $renderingContext->getOriginalTemplatePath();
        }
        if ($originalTemplatePath !== null && $originalTemplatePath !== '') {
            $message .= ' -> ' . $originalTemplatePath;
        }
        throw new Exception($message, $code);
    }
}
