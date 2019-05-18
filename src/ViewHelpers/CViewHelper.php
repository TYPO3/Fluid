<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = false;

    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('s', 'string', 'A string argument');
        $this->registerArgument('i', 'int', 'An integer argument');
        $this->registerArgument('f', 'float', 'A float argument');
        $this->registerArgument('b', 'bool', 'A boolean argument');
        $this->registerArgument('a', 'array', 'An array argument');
        $this->registerArgument('dt', \DateTime::class, 'A DateTime object argument');
        $this->registerArgument('return', 'bool', 'Return the arguments array, do not encode');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $data = [
            'string' => $arguments['s'],
            'int' => $arguments['i'],
            'float' => $arguments['f'],
            'bool' => $arguments['b'],
            'array' => $arguments['a'],
            'datetime' => ($arguments['dt'] instanceof \DateTime ? $arguments['dt']->format('U') : null)
        ];
        if ($arguments['return']) {
            return $data;
        }
        return json_encode($data, JSON_PRETTY_PRINT);
        /*'String: ' . var_export($arguments['s'], true) . ', Int: ' . var_export($arguments['i'], true) . ', Float: ' . var_export($arguments['f'], true) .
            ', Bool: ' . var_export($arguments['b'], true) . ', Array: ' . json_encode($arguments['a']) .
            ', DateTime: ' . ($arguments['dt'] instanceof \DateTime ? $arguments['dt']->format('U') : 'null');*/
    }
}