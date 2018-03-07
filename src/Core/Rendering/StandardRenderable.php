<?php
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class StandardRenderable
 */
class StandardRenderable extends AbstractRenderable
{
    /**
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public function render(RenderingContextInterface $renderingContext)
    {
        return $this->getNode()->evaluate($renderingContext);
    }
}
