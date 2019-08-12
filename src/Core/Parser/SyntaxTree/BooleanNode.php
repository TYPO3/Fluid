<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which is used inside boolean arguments
 */
class BooleanNode extends AbstractComponent
{
    protected $escapeOutput = false;

    protected $combiners = ['&&', '||', 'AND', 'OR', 'and', 'or', '&', '|', 'xor', 'XOR'];

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $input NodeInterface, array (of nodes or expression parts) or a simple type that can be evaluated to boolean
     */
    public function __construct($input = null)
    {
        // First, evaluate everything that is not an ObjectAccessorNode, ArrayNode
        // or ViewHelper so we get all text, numbers, comparators and
        // groupers from the text parts of the expression. All other nodes
        // we leave intact for later processing
        if ($input !== null) {
            $this->value = is_string($input) ? trim($input) : $input;
        }
    }

    public function addChild(ComponentInterface $component): ComponentInterface
    {
        if ($component instanceof TextNode || $component instanceof RootNode && $component->isQuoted()) {
            $this->children[] = $component;
        } else {
            parent::addChild($component);
        }
        return $this;
    }

    public function flatten(bool $extractNode = false)
    {
        if ($extractNode && $this->children[0] instanceof TextNode && count($this->children) === 1) {
            return $this->convertToBoolean($this->children[0]->getText());
        }
        return $this;
    }

    public function evaluate(RenderingContextInterface $renderingContext): bool
    {
        $combiner = null;
        $x = null;
        $parts = [];
        $negated = false;

        foreach ($this->getChildren() as $part) {
            $quoted = false;
            if ($part instanceof RootNode) {
                $quoted = $part->isQuoted();
                $part = $part->flatten(true);
            }

            if ($part instanceof TextNode) {
                $part = $part->getText();
            }

            if ($part === '!') {
                $negated = true;
                continue;
            }


            if ($quoted) {
                // Quoted expression parts must always be cast to string
                $part = (string) $part;
            } elseif (is_string($part)) {
                // If not quoted the value may be numeric or a hardcoded true/false (not quoted string)
                $lowered = strtolower($part);
                if ($lowered === 'true') {
                    $part = true;
                } elseif ($lowered === 'false') {
                    $part = false;
                } elseif (is_numeric($lowered)) {
                    $part = $part + 0;
                } elseif (in_array($part, $this->combiners, true)) {
                    // And/or encountered. Evaluate parts so far and assign left value.

                    $evaluatedParts = $this->evaluateParts($parts, $renderingContext);
                    $parts = [];
                    if ($combiner !== null && $x !== null) {
                        // We must evaluate any parts collected so var
                        $x = $this->evaluateAndOr($x, $evaluatedParts, $combiner);
                        $combiner = null;
                    } else {
                        $x = $evaluatedParts;
                        $combiner = $part;
                    }

                    if ($negated) {
                        $x = !$x;
                        $negated = false;
                    }

                    if (($x === false && ($part === '&&' || $part === 'AND' || $part === 'and')) || ($x === true && ($part === '||' || $part === 'OR' || $part === 'or'))) {
                        // If $x is false and condition is AND, or $x is true and condition is OR, then no more
                        // evaluation is required and we can return $x now.
                        return $x;
                    }
                    continue;
                }
            }

            $parts[] = $part;
        }

        if (!empty($parts)) {
            $evaluatedParts = $this->evaluateParts($parts, $renderingContext);
            if ($combiner !== null) {
                return $this->evaluateAndOr($x, $evaluatedParts, $combiner);
            }
            return $negated ? !$evaluatedParts : $evaluatedParts;
        }

        $value = $this->value instanceof ComponentInterface ? $this->value->evaluate($renderingContext) : $this->value;
        return $this->convertToBoolean($value);
    }

    protected function evaluateAndOr($x, $y, string $combiner): bool
    {
        switch ($combiner) {

            case '||';
            case 'or':
            case 'OR':
                return $x || $y;

            case '&':
                return (bool) ((int) $x & (int) $y);

            case '|':
                return (bool) ((int) $x | (int) $y);

            case 'xor':
            case 'XOR':
                return (bool) ((int) $x XOR (int) $y);

            case '&&':
            case 'and':
            case 'AND':
            default:
                return $x && $y;
        }
    }

    protected function evaluateParts(array $parts, RenderingContextInterface $renderingContext): bool
    {
        $numberOfParts = count($parts);
        $x = null;
        if ($numberOfParts === 3) {
            // Reduce the verdict to one entry in $parts if we've collected enough to evaluate.
            // Future loops may re-
            $x = $parts[0] instanceof ComponentInterface ? $parts[0]->evaluate($renderingContext) : $parts[0];
            $y = $parts[2] instanceof ComponentInterface ? $parts[2]->evaluate($renderingContext) : $parts[2];
            $comparator = (string) ($parts[1] instanceof ComponentInterface ? $parts[1]->evaluate($renderingContext) : $parts[1]);
            return $this->evaluateCompare($x, $y, $comparator);
        } elseif ($numberOfParts === 1) {
            return $this->convertToBoolean(
                $parts[0] instanceof ComponentInterface ? $parts[0]->evaluate($renderingContext) : $parts[0]
            );
        }
        return !empty($parts);
    }

    /**
     * Compare two variables based on a specified comparator
     *
     * @param mixed $x
     * @param mixed $y
     * @param string $comparator
     * @return bool
     */
    protected function evaluateCompare($x, $y, string $comparator): bool
    {
        // enforce strong comparison for comparing two objects
        if ($comparator === '==' && is_object($x) && is_object($y)) {
            $comparator = '===';
        } elseif ($comparator === '!=' && is_object($x) && is_object($y)) {
            $comparator = '!==';
        }

        switch ($comparator) {
            case '==':
                $x = ($x == $y);
                break;

            case '===':
                $x = ($x === $y);
                break;

            case '!=':
                $x = ($x != $y);
                break;

            case '!==':
                $x = ($x !== $y);
                break;

            case '<=':
                $x = ($x <= $y);
                break;

            case '>=':
                $x = ($x >= $y);
                break;

            case '<':
                $x = ($x < $y);
                break;

            case '>':
                $x = ($x > $y);
                break;

            case '%':
                if (!is_numeric($x) || !is_numeric($y)) {
                    $x = 0;
                } else {
                    $x = (($x + 0) % ($y + 0));
                }

                break;
        }

        return (bool) $x;
    }

    /**
     * Convert argument strings to their equivalents. Needed to handle strings with a boolean meaning.
     *
     * Must be public and static as it is used from inside cached templates.
     *
     * @param mixed $value Value to be converted to boolean
     * @return boolean
     */
    protected function convertToBoolean($value): bool
    {
        if (is_string($value)) {
            return (strtolower($value) !== 'false' && !empty($value));
        } elseif (is_array($value)) {
            return !empty($value);
        } elseif ($value instanceof \Countable) {
            return count($value) > 0;
        }
        return (bool) $value;
    }
}
