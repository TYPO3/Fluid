<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ExpressionComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\ExpressionException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Cast Expression ViewHelper, seconds as expression type
 */
class CastViewHelper extends AbstractViewHelper implements ExpressionComponentInterface
{
    protected $parts = [];

    protected static $validTypes = [
        'integer', 'boolean', 'string', 'float', 'array', \DateTime::class
    ];

    public function __construct(iterable $parts = [])
    {
        $this->parts = $parts;
    }

    protected function initializeArguments()
    {
        $this->registerArgument('subject', 'mixed', 'Numeric first value to calculate', true);
        $this->registerArgument('as', 'string', 'Type to cast, valid values are: integer, boolean, string, float, array and DateTime', true);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy();
        if (!empty($this->parts)) {
            $parts = $this->parts;
        } else {
            $parts = [$arguments['subject'], 'as', $arguments['as']];
        }
        return $this->evaluateParts($renderingContext, $parts);
    }

    protected function evaluateParts(RenderingContextInterface $renderingContext, iterable $parts)
    {
        $subject = ((array)($parts))[0];
        if (is_string($subject)) {
            $subject = $renderingContext->getVariableProvider()->get($subject) ?? $subject;
        }
        return static::convert($subject, ((array)$parts)[2]);
    }

    /**
     * Requires exactly three parts: X as Y. Match a size of 3 parts
     * and verify the middle part is string "as".
     *
     * @param array $parts
     * @return bool
     */
    public static function matches(array $parts): bool
    {
        $valid = !isset($parts[3]) && ($parts[1] ?? null) === 'as';
        if ($valid && !in_array($parts[2], self::$validTypes, true)) {
            throw new ExpressionException(
                sprintf(
                    'Invalid target conversion type "%s" specified in casting expression "{%s}".',
                    $parts[2],
                    implode(' ', $parts)
                ),
                1559248372
            );
        }
        return $valid;
    }

    /**
     * @param mixed $variable
     * @param string $type
     * @return mixed
     */
    protected static function convert($variable, string $type)
    {
        $value = null;
        if ($type === 'integer') {
            $value = (integer) $variable;
        } elseif ($type === 'boolean') {
            $value = (boolean) $variable;
        } elseif ($type === 'string') {
            $value = (string) $variable;
        } elseif ($type === 'float') {
            $value = (float) $variable;
        } elseif ($type === \DateTime::class) {
            $value = self::convertToDateTime($variable);
        } elseif ($type === 'array') {
            $value = (array) self::convertToArray($variable);
        }
        return $value;
    }

    /**
     * @param mixed $variable
     * @return \DateTime|false
     */
    protected static function convertToDateTime($variable)
    {
        if (!is_numeric($variable)) {
            return new \DateTime($variable);
        }
        return \DateTime::createFromFormat('U', (string) $variable);
    }

    protected static function convertToArray($variable): array
    {
        if (is_array($variable)) {
            return $variable;
        } elseif (is_string($variable) && strpos($variable, ',')) {
            return array_map('trim', explode(',', $variable));
        } elseif (is_object($variable) && $variable instanceof \Iterator) {
            $array = [];
            foreach ($variable as $key => $value) {
                $array[$key] = $value;
            }
            return $array;
        } elseif (is_object($variable) && method_exists($variable, 'toArray')) {
            return $variable->toArray();
        } elseif (is_bool($variable)) {
            return [];
        } else {
            return [$variable];
        }
    }
}
