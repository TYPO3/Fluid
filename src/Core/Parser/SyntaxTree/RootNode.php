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
 * Root node of every syntax tree.
 */
class RootNode extends AbstractComponent
{
    protected $quoted = false;

    public function isQuoted(): bool
    {
        return $this->quoted;
    }

    public function setQuoted(bool $quoted): self
    {
        $this->quoted = $quoted;
        return $this;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $variables = $renderingContext->getVariableProvider();
        foreach ($this->getArguments()->validate()->getArrayCopy() as $name => $value) {
            $variables->add($name, $value);
        }
        return parent::evaluate($renderingContext);
    }

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $this->getArguments()->setRenderingContext($renderingContext)->validate();
        return parent::onOpen($renderingContext);
    }
}
