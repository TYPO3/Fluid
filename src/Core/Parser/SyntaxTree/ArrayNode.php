<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Array Syntax Tree Node. Handles JSON-like arrays.
 */
class ArrayNode extends AbstractComponent
{

    /**
     * An associative array. Each key is a string. Each value is either a literal, or an AbstractNode.
     *
     * @var array
     */
    protected $internalArray = [];

    /**
     * Constructor.
     *
     * @param array $internalArray Array to store
     */
    public function __construct(array $internalArray)
    {
        $this->internalArray = $internalArray;
    }

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollection $argumentCollection = null): array
    {
        $arrayToBuild = [];
        foreach ($this->internalArray as $key => $value) {
            $arrayToBuild[$key] = $value instanceof ComponentInterface ? $value->execute($renderingContext, $value->getArguments()->setRenderingContext($renderingContext)) : $value;
        }
        return $arrayToBuild;
    }

    /**
     * INTERNAL; DO NOT CALL DIRECTLY!
     *
     * @return array
     */
    public function getInternalArray(): array
    {
        return $this->internalArray;
    }
}
