<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * HTML container tag ViewHelper
 *
 * Intended for use as aliased ViewHelper so that <html>
 * tags will be handled by this ViewHelper class. Allows
 * Fluid to extract namespaces from <html> tags and if
 * so instructed, not render the <html> tag itself but
 * only the child content.
 *
 * @api
 */
class HtmlViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    protected $shouldRenderTag = true;

    public function postParse(array $arguments, ?array $definitions, ParsedTemplateInterface $parsedTemplate, RenderingContextInterface $renderingContext): NodeInterface
    {
        $this->shouldRenderTag = ($arguments['data-namespace-typo3-fluid'] ?? null) === 'true';
        foreach ($arguments as $name => $value) {
            $parts = explode(':', $name);
            if ($parts[0] === 'xmlns' && isset($parts[1]) && strncmp('http://typo3.org/ns/', $value, 20) === 0) {
                $renderingContext->getViewHelperResolver()->addNamespace($parts[1], str_replace('/', '\\', substr($value, 20)));
                unset($arguments[$name]);
            }
        }
        $this->setParsedArguments($arguments);
        return $this;
    }

    public function render()
    {
        $arguments = $this->arguments;
        $content = $this->renderChildren();
        if (!$this->shouldRenderTag) {
            return $content;
        }

        $tagBuilder = new TagBuilder('html');
        $tagBuilder->addAttributes($arguments);
        $tagBuilder->setContent($content);
        return $tagBuilder->render();
    }

    public function validateAdditionalArgument(string $argumentName): bool
    {
        return true;
    }

    public function validateAdditionalArguments(array $arguments)
    {
        // Allows any and all arbitrary arguments regardless of naming
    }
}
