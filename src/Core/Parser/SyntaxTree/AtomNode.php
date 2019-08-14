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
 * Atom Node
 *
 * Represents an Atom used in a template.
 */
class AtomNode extends AbstractComponent
{
    protected $escapeOutput = false;

    protected $file;
    
    public function setName(?string $name): ComponentInterface
    {
        $this->name = $name;
        return $this;
    }

    public function setFile($file): ComponentInterface
    {
        $this->file = $file;
        return $this;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $atom = $renderingContext->getTemplateParser()->parseFile($this->file);
        $arguments = clone $atom->getArguments();
        $arguments->assignAll($this->getArguments()->getAllRaw() + $renderingContext->getVariableProvider()->getAll());
        return $atom->setArguments($arguments)->evaluate($renderingContext);
    }

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $this->getArguments()->setRenderingContext($renderingContext);
        return parent::onOpen($renderingContext);
    }
}
