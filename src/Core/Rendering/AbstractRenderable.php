<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;

/**
 * Class AbstractRenderable
 */
abstract class AbstractRenderable extends AbstractComponent implements RenderableInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var NodeInterface
     */
    protected $node;

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null)
    {
        return $this->render($renderingContext);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return RenderableClosure
     */
    public function setName(string $name): RenderableInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @return RenderableClosure
     */
    public function setNode(NodeInterface $node): RenderableInterface
    {
        $this->node = $node;
        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getNode(): NodeInterface
    {
        return $this->node ? $this->node : new TextNode(sprintf('%s (%s)', static::class, $this->name));
    }
}
