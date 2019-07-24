<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Argument Collection
 *
 * Acts as container around a set of arguments and associated
 * ArgumentDefinition and their values.
 *
 * Contains the API used for validating and converting arguments.
 */
class ArgumentCollection implements ArgumentCollectionInterface, \ArrayAccess, \Iterator
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
        $this->createInternalArguments();
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
        if (!$value instanceof BooleanNode && isset($this->definitions[$name]) && ($type = $this->definitions[$name]->getType()) && ($type === 'bool' || $type === 'boolean')) {
            $value = is_bool($value) || is_numeric($value) || is_null($value) ? (bool) $value : new BooleanNode($value);
        }
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

    public function current()
    {
        return current($this->arguments);
    }

    public function next()
    {
        return next($this->arguments);
    }

    public function key()
    {
        return key($this->arguments);
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function rewind()
    {
        reset($this->arguments);
    }

    /**
     * Creates arguments by padding with missing+optional arguments
     * and casting or creating BooleanNode where appropriate. Input
     * array may not contain all arguments - output array will.
     */
    protected function createInternalArguments(): void
    {
        $missingArguments = [];
        foreach ($this->definitions as $name => $definition) {
            $argument = $this->arguments[$name] ?? null;
            if ($definition->isRequired() && !isset($argument)) {
                // Required but missing argument, causes failure (delayed, to report all missing arguments at once)
                $missingArguments[] = $name;
            } elseif (!isset($argument)) {
                // Argument is optional (required filtered out above), fit it with the default value
                $argument = $definition->getDefaultValue();
            } elseif (($type = $definition->getType()) && ($type === 'bool' || $type === 'boolean')) {
                // Cast the value or create a BooleanNode
                $argument = is_bool($argument) || is_numeric($argument) || is_null($argument) ? (bool)$argument : new BooleanNode($argument);
            }
            $this->arguments[$name] = $argument;
        }
        if (!empty($missingArguments)) {
            throw new Exception('Required argument(s) not provided: ' . implode(', ', $missingArguments), 1558533510);
        }
    }
}
