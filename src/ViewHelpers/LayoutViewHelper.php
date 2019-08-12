<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * With this tag, you can select a layout to be used for the current template.
 *
 * = Examples =
 *
 * <code>
 * <f:layout name="main" />
 * </code>
 * <output>
 * (no output)
 * </output>
 *
 * DEPRECATION INFORMATION
 *
 * This ViewHelper is deprecated - the concept of Layouts is entirely deprecated since
 * Fluid 3.0 and will be removed in 4.0. The replacement for the Layout concept is to
 * use an "Atom" registered with namespace and path(s). When used, and if constructed
 * the same way a Layout is constructed today (with a large body and rendering sections),
 * the Atom then works exactly like a Layout did in versions below 3.0.
 *
 * @deprecated Will be removed in Fluid 4.0
 */
class LayoutViewHelper extends AbstractViewHelper
{
    protected $name = 'layoutName';

    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of layout to use. If none given, "Default" is used.', false, 'Default');
    }

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $container = $renderingContext->getViewHelperVariableContainer();
        $allowed = $container->get(self::class, 'allowed') ?? true;
        if ($allowed) {
            // f:layout is not yet active and is allowed to extract the layout and add add all nodes it contains.
            // Prevents recursion when f:layout is used in both template and layout files.
            $container->addOrUpdate(self::class, 'allowed', false);
            $layoutPathAndFilename = $renderingContext->getTemplatePaths()->getLayoutPathAndFilename(
                $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy()['name']
            );
            $parsedLayout = $renderingContext->getTemplateParser()->parseFile($layoutPathAndFilename);
            $container->addOrUpdate(self::class, 'true', false);
            $this->children = $parsedLayout->getChildren();
        }
        return parent::onOpen($renderingContext);
    }
}
