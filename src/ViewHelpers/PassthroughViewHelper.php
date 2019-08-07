<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\SequencingComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\Sequencer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper;

/**
 * Parsing pass-through ViewHelper
 * 
 * When used in tag mode, does not parse the tag contents
 * as Fluid code - instead, outputs it as raw text.
 */
class PassthroughViewHelper extends AbstractViewHelper implements SequencingComponentInterface
{
    protected function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('escape', 'bool', 'Set to false to not escape contents', false, true);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        if ($this->getArguments()['escape'] ?? true) {
            return (new HtmlspecialcharsViewHelper())->setChildren($this->getChildren())->evaluate($renderingContext);
        }
        return $this->evaluateChildren($renderingContext);
    }

    public function sequence(Sequencer $sequencer, ?string $namespace, string $method): void
    {
        $sequencer->sequenceUntilClosingTagAndIgnoreNested($this, $namespace, $method);
    }
}
