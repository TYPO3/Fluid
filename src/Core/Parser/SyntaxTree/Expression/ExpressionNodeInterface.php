<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Interface for shorthand expression node types
 */
interface ExpressionNodeInterface extends NodeInterface
{
    /**
     * Evaluate the whitespace-split parts of the expression.
     *
     * @param RenderingContextInterface $renderingContext
     * @param iterable $parts
     * @return mixed
     */
    public function evaluateParts(RenderingContextInterface $renderingContext, iterable $parts);

    /**
     * Must return TRUE if the parts (split to array by inline
     * tokens and spaces) matches the type of expression.
     *
     * @param array $parts
     * @return bool
     */
    public static function matches(array $parts): bool;
}
