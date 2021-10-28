<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ExpressionComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Ternary Condition Node - allows the shorthand version
 * of a condition to be written as `{var ? thenvar : elsevar}`
 */
class NullcoalescingViewHelper extends AbstractViewHelper implements ExpressionComponentInterface
{
    protected $parts = [];

    public function __construct(iterable $parts = [])
    {
        $this->parts = $parts;
    }

    protected function initializeArguments()
    {
        $this->registerArgument('a', 'mixed', 'Anythong that can have a value or null', true);
        $this->registerArgument('b', 'mixed', 'Fallback value', true);
    }

    public static function matches(array $parts): bool
    {
        var_dump('matches');
        return isset($parts[2]) && strpos('??', $parts[1]) !== false;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param string $expression
     * @param array $matches
     * @return mixed
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy();
        $parts = empty($this->parts) ? [$arguments['a'], $arguments['b']] : $this->parts;

        var_dump($parts);
        var_dump($renderingContext->getVariableProvider()->getAll());

        foreach($parts as $part) {
            $value = static::getTemplateVariableOrValueItself($part, $renderingContext);
            var_dump($value);
            if(!is_null($value)) {
                return $value;
            }
        }

        return null;
    }


    /**
     * @param mixed $candidate
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    protected static function getTemplateVariableOrValueItself($candidate, RenderingContextInterface $renderingContext)
    {
        if(is_null($candidate)) {
            return null;
        }
        if (is_numeric($candidate)) {
            return $candidate;
        }

        if (mb_strpos($candidate, '\'') === 0) {
            return trim($candidate, '\'');
        } elseif (mb_strpos($candidate, '"') === 0) {
            return trim($candidate, '"');
        }
        return $renderingContext->getVariableProvider()->get($candidate);
    }
}
