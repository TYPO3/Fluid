<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Escaping Node - wraps all content that must be escaped before output.
 */
class EscapingNode extends AbstractNode
{

    /**
     * Node to be escaped
     *
     * @var ComponentInterface
     */
    protected $node;

    /**
     * Constructor.
     *
     * @param ComponentInterface $node
     */
    public function __construct(ComponentInterface $node)
    {
        $this->node = $node;
    }

    /**
     * Return the value associated to the syntax tree.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $evaluated = $this->node->execute($renderingContext);
        if (is_string($evaluated) || (is_object($evaluated) && method_exists($evaluated, '__toString'))) {
            return htmlspecialchars((string) $evaluated, ENT_QUOTES);
        }
        return (string)$evaluated;
    }

    /**
     * @return ComponentInterface
     */
    public function getNode(): ComponentInterface
    {
        return $this->node;
    }

    /**
     * NumericNode does not allow adding child nodes, so this will always throw an exception.
     *
     * @param ComponentInterface $childNode The sub node to add
     * @throws Exception
     * @return ComponentInterface
     */
    public function addChild(ComponentInterface $childNode): ComponentInterface
    {
        $this->node = $childNode;
        return $this;
    }
}
