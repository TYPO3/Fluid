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
 * Root node of every syntax tree.
 *
 * @internal
 * @todo Make class final.
 */
class RootNode extends AbstractNode
{
    /**
     * Evaluate the root node, by evaluating the subtree.
     *
     * @return mixed Evaluated subtree
     */
    public function evaluate(RenderingContextInterface $renderingContext): mixed
    {
        return $this->evaluateChildNodes($renderingContext);
    }

    /**
     * @todo: Similar to TemplateCompiler->convertSubNodes(). See its todo.
     */
    public function convert(TemplateCompiler $templateCompiler): array
    {
        switch (count($this->getChildNodes())) {
            case 0:
                return [
                    'initialization' => '',
                    'execution' => 'NULL',
                ];
            case 1:
                $childNode = current($this->getChildNodes());
                if ($childNode instanceof NodeInterface) {
                    return $childNode->convert($templateCompiler);
                }
                // no break
            default:
                $outputVariableName = $templateCompiler->variableName('output');
                $initializationPhpCode = sprintf('%s = \'\';', $outputVariableName) . chr(10);

                foreach ($this->getChildNodes() as $childNode) {
                    $converted = $childNode->convert($templateCompiler);

                    $initializationPhpCode .= $converted['initialization'] . chr(10);
                    $initializationPhpCode .= sprintf('%s .= %s;', $outputVariableName, $converted['execution']) . chr(10);
                }

                return [
                    'initialization' => $initializationPhpCode,
                    'execution' => $outputVariableName,
                ];
        }
    }
}
