<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\EmbeddedComponentInterface;
use TYPO3Fluid\Fluid\Component\TransparentComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Entry node. Used to represent either the root
 * entry point of a template tree, or a section
 * within the template tree. Common for EntryNode
 * is that:
 *
 * - It supports arguments that become variables
 * - It supports setting the name of the node which
 *   allows it to be returned from getNamedChild.
 * - It is an EmbeddedComponent which means that it
 *   is only rendered when explicitly calling the
 *   evaluate() method on the instance itself, it
 *   is not rendered when rendering the parent node.
 *
 * EntryNode instances can be resolved directly
 * from getTypedChildren to extract all sections
 * within a tree; or getNamedChild can be used
 * to extract an EntryNode (or an EntryNode child
 * of another EntryNode to any nesting depth by
 * using dotted path as argument for getNamedChild).
 */
class EntryNode extends AbstractComponent implements EmbeddedComponentInterface, TransparentComponentInterface
{
    protected $escapeOutput = false;

    public function setName(?string $name): ComponentInterface
    {
        $this->name = $name;
        return $this;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $renderingContext = clone $renderingContext;
        $renderingContext->setVariableProvider(
            $renderingContext->getVariableProvider()->getScopeCopy(
                $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy()
            )
        );
        return parent::evaluate($renderingContext);
    }

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $this->getArguments()->setRenderingContext($renderingContext)->validate();
        return parent::onOpen($renderingContext);
    }
}
