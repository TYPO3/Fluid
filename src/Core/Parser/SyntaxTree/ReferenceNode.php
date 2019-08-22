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
 * Reference Node
 *
 * Inserts a reference to render a named child of the template
 * that's currently being rendered. Takes over the responsibility
 * of f:render to render sections.
 */
class ReferenceNode extends AbstractComponent
{
    protected $escapeOutput = false;

    protected $target = '';

    public function __construct(string $target)
    {
        $this->target = $target;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $component = $renderingContext->getRenderer()->getComponentBeingRendered()->getNamedChild($this->target);
        $component->getArguments()->assignAll(
            $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy()
            + $renderingContext->getVariableProvider()->getAll()
        );
        return $component->evaluate($renderingContext);
    }

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $this->getArguments()->setRenderingContext($renderingContext);
        return parent::onOpen($renderingContext);
    }
}
