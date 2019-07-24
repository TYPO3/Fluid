<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Fixture ViewHelper with arguments of all basic types.
 */
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

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null)
    {
        $arguments = ($arguments ?? $this->getArguments())->evaluate($renderingContext);
        $data = [
            'string' => $arguments['s'],
            'int' => $arguments['i'],
            'float' => $arguments['f'],
            'bool' => $arguments['b'],
            'array' => $arguments['a'],
            'datetime' => ($arguments['dt'] instanceof \DateTime ? $arguments['dt']->format('U') : null),
            'child' => $this->evaluateChildren($renderingContext),
        ];
        if ($arguments['return']) {
            return $data;
        }
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}