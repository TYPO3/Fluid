<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Interface for shorthand expression node types
 */
interface ExpressionNodeInterface extends NodeInterface
{
    /**
     * Evaluates the expression by delegating it to the
     * resolved ExpressionNode type.
     */
    public function evaluate(RenderingContextInterface $renderingContext): mixed;

    /**
     * Evaluate expression, static version. Should return
     * the exact same value as evaluate() but should be
     * able to do so in a statically called context.
     */
    public static function evaluateExpression(RenderingContextInterface $renderingContext, string $expression, array $matches): mixed;

    /**
     * Compiles the ExpressionNode, returning an array with
     * exactly two keys which contain strings:
     *
     * - "initialization" which contains variable initializations
     * - "execution" which contains the execution (that uses the variables)
     *
     * The expression and matches can be read from the local
     * instance - and the RenderingContext and other APIs
     * can be accessed via the TemplateCompiler.
     *
     * @return array{initialization: string, execution: string}
     */
    public function compile(TemplateCompiler $templateCompiler): array;

    /**
     * Getter for returning the expression before parsing.
     */
    public function getExpression(): string;

    public function getMatches(): array;
}
