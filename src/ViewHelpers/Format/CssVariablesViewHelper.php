<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to provide fluid variables as CSS variables.
 *
 * The css variable names are created from the array keys.
 * You can add a custom prefix to the CSS variable name.
 *
 * By default, the variables are defined as `:root`.
 * The CSS selector can be customized to any string (e.g. `.my-select, #id .other-selector`.
 *
 * Examples
 * ========
 *
 * Converting a view variable
 * ------------------------
 *
 * ::
 *
 *    {someArray -> f:format.cssVariables()}
 *
 * ``["array","values"]``
 * Depending on the value of ``{someArray}``.
 *
 * Associative array
 * -----------------
 *
 * ::
 *
 *    {f:format.cssVariables(value: {foo: 'bar', bar: 'baz'})}
 *
 * ``:root {
 * --foo: bar;
 * --bar: baz;
 * }``
 *
 * Nested array with prefix and custom selector
 * ----------------------------------------
 *
 * ::
 *
 *    {f:format.cssVariables(value: {foo: 'bar', bar: {baz: 'qux'}}, prefix: 'color', selector: '.my-class')}
 *
 * ``.my-class {
 *  --color-foo: bar;
 *  --color-bar-baz: qux;
 *  }``
 */
final class CssVariablesViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'Input array which should be provided as CSS variables');
        $this->registerArgument('prefix', 'string', 'Prefix used in the CSS variables name', false, '');
        $this->registerArgument('selector', 'string', 'Define CSS selector/s for the CSS variables', false, ':root');
    }

    public function render(): string
    {
        if (empty($this->arguments['selector'])) {
            return '';
        }

        $value = $this->arguments['value'];

        if ($value === null) {
            $value = $this->renderChildren();
        }

        if (!is_iterable($value)) {
            return '';
        }

        $prefix = is_string($this->arguments['prefix']) ? $this->arguments['prefix'] : '';
        if (!empty($prefix)) {
            $prefix .= '-';
        }

        $content = $this->arguments['selector'] . ' {' . PHP_EOL;
        $content .= $this->buildVariables($value, $prefix);
        $content .= '}';

        return $content;
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }

    private function buildVariables(iterable $variables, string $prefix = ''): string
    {
        $content = '';

        foreach ($variables as $key => $value) {
            if (is_iterable($value)) {
                $content .= self::buildVariables($value, $prefix . $key . '-');
            } elseif (is_scalar($value) || $value instanceof \Stringable) {
                $content .= '--' . strtolower($prefix . $key) . ': ' . $value . ';' . PHP_EOL;
            }
        }
        return $content;
    }
}
