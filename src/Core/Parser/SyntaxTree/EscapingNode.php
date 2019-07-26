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
 * Escaping Node - wraps all content that must be escaped before output.
 */
class EscapingNode extends AbstractComponent
{
    /**
     * Node to be escaped
     *
     * @var ComponentInterface
     */
    protected $node;

    public function __construct(ComponentInterface $node)
    {
        $this->node = $node;
    }

    public function getName(): ?string
    {
        return $this->node->getName();
    }

    public function addChild(ComponentInterface $component): ComponentInterface
    {
        $this->node = $component;
        return $this;
    }

    public function execute(RenderingContextInterface $renderingContext)
    {
        $evaluated = $this->node->execute($renderingContext);
        if (is_string($evaluated) || (is_object($evaluated) && method_exists($evaluated, '__toString'))) {
            return htmlspecialchars((string) $evaluated, ENT_QUOTES);
        }
        return (string) $evaluated;
    }
}
