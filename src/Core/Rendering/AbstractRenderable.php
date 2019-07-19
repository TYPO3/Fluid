<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
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
     * @var ComponentInterface
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
     * @param ComponentInterface $node
     * @return RenderableClosure
     */
    public function setNode(ComponentInterface $node): RenderableInterface
    {
        $this->node = $node;
        return $this;
    }

    /**
     * @return ComponentInterface
     */
    public function getNode(): ComponentInterface
    {
        return $this->node ? $this->node : new TextNode(sprintf('%s (%s)', static::class, $this->name));
    }
}
