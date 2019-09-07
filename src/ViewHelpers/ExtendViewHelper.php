<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\TransparentComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Makes a template extend from an Atom identified by name.
 *
 * Extending from an Atom means:
 *
 * 1) Sections from the Atom are imported to current template
 * 2) Any body content in the Atom which is *not* enclosed in
 *    a section is imported at the place where this ViewHelper
 *    is used.
 * 3) Sections contained within the Atom technically do not
 *    exist *before* this ViewHelper is used, in terms of
 *    parsing sequence (order matters).
 * 4) Any embedded documentation from the extended Atom becomes
 *    the documentation for this template (but can be overwritten).
 *
 * Note that sections from the Atom can then both be extracted
 * from the template importing it, and the template can overwrite
 * any sections coming from the Atom by simply declaring a new
 * section of the same name, after the f:extends usage.
 */
class ExtendViewHelper extends AbstractViewHelper implements TransparentComponentInterface
{
    protected $escapeOutput = false;

    /**
     * @var ComponentInterface|null
     */
    protected $atom;

    public function initializeArguments()
    {
        $this->registerArgument('atom', 'string', 'Name of Atom to extend', true);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return null;
    }

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        list ($namespace, $atomName) = explode(':', $this->getArguments()->setRenderingContext($renderingContext)['atom']);
        $atom = $renderingContext->getViewHelperResolver()->resolveAtom($namespace, $atomName);
        foreach ($atom->getChildren() as $child) {
            $this->addChild($child);
        }
        $this->atom = $atom;
        return parent::onOpen($renderingContext);
    }

    public function getAtom(): ?ComponentInterface
    {
        return $this->atom;
    }
}
