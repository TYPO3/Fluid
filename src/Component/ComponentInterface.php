<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinitionInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Basic Fluid component interface
 *
 * Implemented by any class that is capable of being rendered
 * in Fluid with or without arguments.
 */
interface ComponentInterface
{
    /**
     * @param RenderingContextInterface $renderingContext
     * @param ArgumentCollectionInterface|null $arguments
     * @return self
     */
    public function onOpen(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null): self;

    /**
     * @param RenderingContextInterface $renderingContext
     * @return self
     */
    public function onClose(RenderingContextInterface $renderingContext): self;

    /**
     * Evaluate the component by passing the execution context
     * which contains a rendering context and arguments.
     *
     * @param RenderingContextInterface $renderingContext
     * @param ArgumentCollectionInterface|null $arguments
     * @return mixed
     */
    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null);

    /**
     * Adds a parameter to the parameterized component.
     *
     * @param ArgumentDefinitionInterface $definition
     * @return ComponentInterface
     */
    public function addArgumentDefinition(ArgumentDefinitionInterface $definition): self;

    /**
     * Creates a collection of arguments based on parameter
     * definitions of this component, ready to be filled with
     * arguments that will be passed to evaluateWithArguments()
     *
     * @return ArgumentCollectionInterface
     */
    public function createArgumentDefinitions(): ArgumentCollectionInterface;

    public function getName(): ?string;

    public function getArguments(): ?ArgumentCollectionInterface;

    public function addChild(ComponentInterface $component): self;

    public function getNamedChild(string $name): ComponentInterface;

    public function getChildren(): iterable;
}
