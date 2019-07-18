<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for nodes based on (shorthand) expressions.
 */
abstract class AbstractExpressionNode extends AbstractNode implements ExpressionNodeInterface
{
    /**
     * @var array
     */
    protected $parts = [];

    public function __construct(iterable $parts)
    {
        $this->parts = $parts;
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
            return $this->evaluateParts($renderingContext, $this->parts);
        } catch (ExpressionException $exception) {
            return $renderingContext->getErrorHandler()->handleExpressionError($exception);
        }
    }

    /**
     * @param mixed $candidate
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    protected static function getTemplateVariableOrValueItself($candidate, RenderingContextInterface $renderingContext)
    {
        $suspect = $renderingContext->getVariableProvider()->getByPath($candidate);
        if (null === $suspect) {
            return $candidate;
        }
        return $suspect;
    }
}
