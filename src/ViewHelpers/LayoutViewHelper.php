<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\PostParseInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;

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
 * @api
 */
class LayoutViewHelper extends AbstractViewHelper
{
    use ParserRuntimeOnly;

    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of layout to use. If none given, "Default" is used.');
    }

    public function postParse(array $arguments, ?array $definitions, ParsedTemplateInterface $parsedTemplate, RenderingContextInterface $renderingContext): NodeInterface
    {
        parent::postParse($arguments, $definitions, $parsedTemplate, $renderingContext);
        $variableContainer = $parsedTemplate->getVariableContainer();
        if (isset($arguments['name'])) {
            $layoutNameNode = $arguments['name'];
        } else {
            $layoutNameNode = 'Default';
        }

        $variableContainer->add('layoutName', $layoutNameNode);
        return new TextNode('');
    }

    /**
     * On the post parse event, add the "layoutName" variable to the variable container so it can be used by the TemplateView.
     *
     * @param ViewHelperNode $node
     * @param array $arguments
     * @param VariableProviderInterface $variableContainer
     * @return void
     */
    public static function postParseEvent(
        ViewHelperNode $node,
        array $arguments,
        VariableProviderInterface $variableContainer
    ): void {
        if (isset($arguments['name'])) {
            $layoutNameNode = $arguments['name'];
        } else {
            $layoutNameNode = 'Default';
        }

        $variableContainer->add('layoutName', $layoutNameNode);
    }
}
