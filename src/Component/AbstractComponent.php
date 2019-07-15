<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component;

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinitionInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base Component Class
 *
 * Contains standard implementations for some of the more
 * universal methods a Component supports, e.g. handling
 * of child components and resolving of named children.
 */
abstract class AbstractComponent implements ComponentInterface
{
    /**
     * Unnamed children indexed by numeric position in array
     *
     * @var ComponentInterface[]
     */
    protected $children = [];

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var ArgumentCollectionInterface|null
     */
    protected $arguments;

    protected $argumentDefinitions = [];

    public function onOpen(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null): ComponentInterface
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function onClose(RenderingContextInterface $renderingContext): ComponentInterface
    {
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function addArgumentDefinition(ArgumentDefinitionInterface $definition): ComponentInterface
    {
        $this->argumentDefinitions[$definition->getName()] = $definition;
        return $this;
    }

    public function createArgumentDefinitions(): ArgumentCollectionInterface
    {
        return new ArgumentCollection($this->argumentDefinitions);
    }

    public function getArguments(): ?ArgumentCollectionInterface
    {
        return $this->arguments;
    }

    public function addChild(ComponentInterface $component): ComponentInterface
    {
        $this->children[] = $component;
        return $this;
    }

    public function getNamedChild(string $name): ComponentInterface
    {
        foreach ($this->children as $child) {
            if ($child->getName() === $name) {
                return $child;
            }
        }
        throw new ChildNotFoundException(sprintf('Child with name "%s" not found', $name), 1562757835);
    }

    public function getChildren(): iterable
    {
        return $this->children;
    }
}