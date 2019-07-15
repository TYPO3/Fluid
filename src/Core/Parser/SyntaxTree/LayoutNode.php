<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Layout Node - child-less node to designate a
 * Layout to be used by the template.
 */
class LayoutNode extends AbstractNode
{
    protected $name = 'layoutName';
    protected $layoutName;

    public function __construct($layoutName)
    {
        $this->layoutName = $layoutName;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $this->layoutName instanceof ComponentInterface ? $this->layoutName->execute($renderingContext) : $this->layoutName;
    }

}