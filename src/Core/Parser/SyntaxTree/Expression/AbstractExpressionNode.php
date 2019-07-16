<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableExtractor;

/**
 * Base class for nodes based on (shorthand) expressions.
 */
abstract class AbstractExpressionNode extends AbstractNode implements ExpressionNodeInterface
{

    /**
     * Contents of the text node
     *
     * @var string
     */
    protected $expression;

    /**
     * @var array
     */
    protected $matches = [];

    /**
     * Constructor.
     *
     * @param string $expression The original expression that created this node.
     * @param array $matches Matches extracted from expression
     * @throws Exception
     */
    public function __construct(string $expression, array $matches)
    {
        $this->expression = trim($expression, " \t\n\r\0\x0b");
        $this->matches = $matches;
    }

    /**
     * Evaluates the expression stored in this node, in the context of $renderingcontext.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        try {
            return static::evaluateExpression($renderingContext, $this->expression, $this->matches);
        } catch (ExpressionException $exception) {
            return $renderingContext->getErrorHandler()->handleExpressionError($exception);
        }
    }

    /**
     * Getter for returning the expression before parsing.
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @param string $part
     * @return string
     */
    protected static function trimPart(string $part): string
    {
        return trim($part, " \t\n\r\0\x0b{}");
    }

    /**
     * @param mixed $candidate
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    protected static function getTemplateVariableOrValueItself($candidate, RenderingContextInterface $renderingContext)
    {
        $variables = $renderingContext->getVariableProvider()->getAll();
        $extractor = new VariableExtractor();
        $suspect = $extractor->getByPath($variables, $candidate);
        if (null === $suspect) {
            return $candidate;
        }
        return $suspect;
    }
}
