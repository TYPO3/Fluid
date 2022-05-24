<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

class ConstraintSyntaxTreeNode extends ViewHelperNode
{
    /**
     * @var VariableProviderInterface
     */
    protected $variableContainer;

    public $callProtocol = [];

    public function __construct(VariableProviderInterface $variableContainer)
    {
        $this->variableContainer = $variableContainer;
    }

    public function evaluateChildNodes(RenderingContextInterface $renderingContext)
    {
        $identifiers = (array)$this->variableContainer->getAllIdentifiers();
        $callElement = [];
        foreach ($identifiers as $identifier) {
            $callElement[$identifier] = $this->variableContainer->get($identifier);
        }
        $this->callProtocol[] = $callElement;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return '';
    }
}
