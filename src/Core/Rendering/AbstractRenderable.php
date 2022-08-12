<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Rendering;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;

abstract class AbstractRenderable implements RenderableInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var NodeInterface|null
     */
    protected $node;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return RenderableInterface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @return RenderableInterface
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;
        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node ?: new TextNode(sprintf('%s (%s)', static::class, $this->name));
    }
}
