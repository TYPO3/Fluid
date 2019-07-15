<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Argument Collection
 *
 * Acts as container around a set of arguments and associated
 * ArgumentDefinition and their values.
 *
 * Contains the API used for validating and converting arguments.
 */
class ArgumentCollection implements ArgumentCollectionInterface, \ArrayAccess
{
    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var ArgumentDefinitionInterface[]
     */
    protected $definitions = [];

    public function __construct(iterable $definitions = [])
    {
        foreach ($definitions as $definition) {
            $this->addDefinition($definition);
        }
    }

    public function getDefinitions(): iterable
    {
        return $this->definitions;
    }

    public function assignAll(iterable $values): ArgumentCollectionInterface
    {
        foreach ($values as $name => $value) {
            $this->assign($name, $value);
        }
        return $this;
    }

    public function evaluate(RenderingContextInterface $renderingContext): iterable
    {
        $evaluated = [];
        foreach ($this->readAll() as $key => $value) {
            $evaluated[$key] = $value instanceof ComponentInterface ? $value->execute($renderingContext) : $value;
        }
        return $evaluated;
    }

    public function assign(string $name, $value): ArgumentCollectionInterface
    {
        $this->arguments[$name] = $value;
        return $this;
    }

    public function readAll(): iterable
    {
        return $this->arguments;
    }

    public function addDefinition(ArgumentDefinitionInterface $definition): ArgumentCollectionInterface
    {
        $argumentName = $definition->getName();
        $this->definitions[$argumentName] = $definition;
        $this->arguments[$argumentName] = $definition->getDefaultValue();
        return $this;
    }

    public function read(string $argumentName)
    {
        $value = $this->arguments[$argumentName] ?? null;
        if ($value === null && isset($this->definitions[$argumentName])) {
            $value = $this->definitions[$argumentName]->getDefaultValue();
        }
        return $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->arguments[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->arguments[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->arguments[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->arguments[$offset]);
    }

}
