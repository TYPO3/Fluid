<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which handles object access. This means it handles structures like {object.accessor.bla}
 *
 * @internal
 * @todo Make class final.
 */
class ObjectAccessorNode extends AbstractNode
{
    /**
     * Object path which will be called. Is a list like "post.name.email"
     */
    protected string $objectPath;

    /**
     * Constructor. Takes an object path as input.
     *
     * The first part of the object path has to be a variable in the
     * VariableProvider.
     *
     * @param string $objectPath An Object Path, like object1.object2.object3
     */
    public function __construct(string $objectPath)
    {
        $this->objectPath = $objectPath;
    }

    /**
     * @internal Internally used for building up cached templates; do not use directly!
     * @return string
     */
    public function getObjectPath(): string
    {
        return $this->objectPath;
    }

    /**
     * Evaluate this node and return the correct object.
     *
     * Handles each part (denoted by .) in $this->objectPath in the following order:
     * - call appropriate getter
     * - call public property, if exists
     * - fail
     *
     * The first part of the object path has to be a variable in the
     * VariableProvider.
     *
     * @return mixed The evaluated object, can be any object type.
     */
    public function evaluate(RenderingContextInterface $renderingContext): mixed
    {
        $variableProvider = $renderingContext->getVariableProvider();
        return match (strtolower($this->objectPath)) {
            '_all' => $variableProvider->getAll(),
            'true' => true,
            'false' => false,
            'null' => null,
            default => $variableProvider->getByPath($this->objectPath),
        };
    }

    public function convert(TemplateCompiler $templateCompiler): array
    {
        return match (strtolower($this->objectPath)) {
            '_all' => [
                'initialization' => '',
                'execution' => '$renderingContext->getVariableProvider()->getAll()',
            ],
            'true' => [
                'initialization' => '',
                'execution' => 'true',
            ],
            'false' => [
                'initialization' => '',
                'execution' => 'false',
            ],
            'null' => [
                'initialization' => '',
                'execution' => 'null',
            ],
            default => [
                'initialization' => '',
                'execution' => sprintf(
                    '$renderingContext->getVariableProvider()->getByPath(\'%s\')',
                    $this->objectPath,
                ),
            ],
        };
    }
}
