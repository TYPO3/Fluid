<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class NullNode extends AbstractNode
{
    /**
     * @var null
     */
    protected $value = null;

    /**
     * Constructor.
     *
     * @param string|null $value value to store in this node
     * @throws Parser\Exception
     */
    public function __construct($value)
    {
        if (!is_null($value)) {
            throw new Parser\Exception('Null node requires an argument of type null, "' . gettype($value) . '" given.');
        }
    }

    /**
     * Return the text associated to the syntax tree. Text from child nodes is
     * appended to the text in the node's own text.
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return null;
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return null;
    }

    public function convert(TemplateCompiler $templateCompiler): array
    {
        return [
            'initialization' => '',
            'execution' => null,
        ];
    }
}
