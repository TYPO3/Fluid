<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\EmbeddedComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\ViewHelpers\ExtendViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ParameterViewHelper;

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
class EntryNode extends AbstractComponent implements EmbeddedComponentInterface
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

    public function onClose(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $this->getArguments()->setRenderingContext($renderingContext);
        foreach ($this->getChildren() as $child) {
            if ($child instanceof ExtendViewHelper) {
                $atom = $child->getAtom();
                foreach ($atom->getArguments()->getDefinitions() as $definition) {
                    $this->getArguments()->addDefinition($definition);
                }
            } elseif ($child instanceof ParameterViewHelper) {
                // The child is a parameter declaration. Use the Component's argument values to create and
                // add a new ArgumentDefinition to this component.
                $arguments = $this->getArguments();
                $context = $arguments->getRenderingContext();
                $parameters = $child->getArguments()->setRenderingContext($context);
                $arguments->addDefinition(
                    new ArgumentDefinition(
                        $parameters['name'],
                        $parameters['type'],
                        $parameters['description'] ?? 'Argument ' . $parameters['name'],
                        $parameters['required'],
                        $parameters['default'] ?? null
                    )
                );
            }
        }
        return $this;
    }
}
