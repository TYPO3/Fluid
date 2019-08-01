<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\EmbeddedComponentInterface;
use TYPO3Fluid\Fluid\Component\SequencingComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\Sequencer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Template Example ViewHelper
 *
 * Allows you to embed a block of usage examples into
 * a template; the tag contents are NOT parsed as Fluid
 * code and will be possible to extract in documentation
 * or design systems.
 *
 * Ignores nested Fluid code except for the closing tag
 * that ends the block.
 */
class ExampleViewHelper extends AbstractViewHelper implements EmbeddedComponentInterface, SequencingComponentInterface
{
    protected $escapeOutput = false;

    protected function initializeArguments()
    {
        $this->registerArgument('title', 'string', 'Optional string title of the code example piece');
    }

    public function sequence(Sequencer $sequencer, ?string $namespace, string $method): void
    {
        $sequencer->sequenceUntilClosingTagAndIgnoreNested($this, $namespace, $method);
    }
}
