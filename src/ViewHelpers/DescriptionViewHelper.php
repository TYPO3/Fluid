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
 * Template Description ViewHelper
 *
 * Allows defining a description for a component.
 *
 * Can be used inside any (named) Component such as a
 * section, or the root of a template file. Can then
 * be retrieved by documentation frameworks or design
 * systems as description for the template, section etc.
 *
 * Ignores nested Fluid code except for the closing tag
 * that ends the block.
 */
class DescriptionViewHelper extends AbstractViewHelper implements EmbeddedComponentInterface, SequencingComponentInterface
{
    protected $escapeOutput = false;

    public function sequence(Sequencer $sequencer, ?string $namespace, string $method): void
    {
        $sequencer->sequenceUntilClosingTagAndIgnoreNested($this, $namespace, $method);
    }
}
