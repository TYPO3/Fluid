<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Argument Collection Interface
 *
 * Implemented by ArgumentCollection returned from Components,
 * and/or custom argument handling returned from third party.
 */
interface ArgumentCollectionInterface
{
    public function __construct(iterable $arguments);

    public function assignAll(iterable $values): self;

    public function assign(string $argumentName, $value): self;

    public function readAll(): iterable;

    public function evaluate(RenderingContextInterface $renderingContext): iterable;

    public function read(string $argumentName);

    public function addDefinition(ArgumentDefinitionInterface $definition): ArgumentCollectionInterface;

    public function getDefinitions(): iterable;
}
