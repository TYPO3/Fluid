<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
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
class ArgumentCollection extends \ArrayObject
{
    /**
     * @var ArgumentDefinition[]
     */
    protected $definitions = [];

    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    public function setRenderingContext(RenderingContextInterface $renderingContext): self
    {
        $this->renderingContext = $renderingContext;
        return $this;
    }

    public function getRenderingContext(): RenderingContextInterface
    {
        return $this->renderingContext;
    }

    public function getDefinitions(): iterable
    {
        return $this->definitions;
    }

    public function assignAll(iterable $values): ArgumentCollection
    {
        foreach ($values as $name => $value) {
            $this[$name] = $value;
        }
        return $this;
    }

    public function getAllRaw(): iterable
    {
        return parent::getArrayCopy();
    }

    public function getRaw(string $argumentName)
    {
        $value = $this[$argumentName] ?? null;
        return $value;
    }

    public function getUndeclaredArgumentsAndValues(): iterable
    {
        return array_diff_key(
            $this->getArrayCopy(),
            $this->definitions
        );
    }

    public function addDefinition(ArgumentDefinition $definition): ArgumentCollection
    {
        $argumentName = $definition->getName();
        $this->definitions[$argumentName] = $definition;
        return $this;
    }

    /**
     * @param iterable|ArgumentDefinition[] $definitions
     * @return ArgumentCollection
     */
    public function setDefinitions(iterable $definitions): ArgumentCollection
    {
        $this->definitions = $definitions;
        return $this;
    }

    public function isArgumentBoolean(string $argumentName): bool
    {
        return isset($this->definitions[$argumentName]) && $this->definitions[$argumentName]->getType() === 'boolean';
    }

    public function toArrayNode(): ArrayNode
    {
        return new ArrayNode((array) $this->getAllRaw());
    }

    public function offsetSet($index, $value)
    {
        if (isset($this->definitions[$index]) && !$value instanceof BooleanNode && $this->definitions[$index]->getType() === 'boolean' && $value !== false && $value !== true) {
            // Note: a switch() was not used here because it makes PHP attempt type coercion and we need strict comparisons.
            if ($value === 1 || $value === 'true' || $value === 'TRUE') {
                $value = true;
            } elseif ($value === null || $value === 0 || $value === 'false' || $value === 'FALSE') {
                $value = false;
            } else {
                $value = new BooleanNode($value);
            }
        }
        parent::offsetSet($index, $value);
    }

    public function offsetGet($offset)
    {
        if (isset($this->definitions[$offset]) && !parent::offsetExists($offset)) {
            return $this->definitions[$offset]->getDefaultValue();
        }
        $value = parent::offsetGet($offset);
        if ($value instanceof ComponentInterface) {
            $value = $value->evaluate($this->renderingContext);
        }
        return $value;
    }

    public function getArrayCopy(): array
    {
        $data = [];
        foreach (parent::getArrayCopy() + $this->definitions as $name => $_) {
            $data[$name] = $this[$name];
        }
        return $data;
    }

    /**
     * Creates arguments by padding with missing+optional arguments
     * and casting or creating BooleanNode where appropriate. Input
     * array may not contain all arguments - output array will.
     */
    public function validate(): self
    {
        $missingArguments = [];
        foreach ($this->definitions as $name => $definition) {
            if ($definition->isRequired() && !parent::offsetExists($name)) {
                // Required but missing argument, causes failure (delayed, to report all missing arguments at once)
                $missingArguments[] = $name;
            }
        }
        if (!empty($missingArguments)) {
            throw new Exception('Required argument(s) not provided: ' . implode(', ', $missingArguments), 1558533510);
        }
        return $this;
    }
}
