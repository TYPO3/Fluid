<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Array Syntax Tree Node. Handles JSON-like arrays.
 */
class ArrayNode extends AbstractNode
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

    /**
     * Evaluate the array and return an evaluated array
     *
     * @param RenderingContextInterface $renderingContext
     * @return array An associative array with literal values
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arrayToBuild = [];
        foreach ($this->internalArray as $key => $value) {
            $arrayToBuild[$key] = $value instanceof NodeInterface ? $value->evaluate($renderingContext) : $value;
        }
        return $arrayToBuild;
    }

    /**
     * INTERNAL; DO NOT CALL DIRECTLY!
     *
     * @return array
     */
    public function getInternalArray()
    {
        return $this->internalArray;
    }
}
