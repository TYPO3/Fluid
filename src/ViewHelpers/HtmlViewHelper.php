<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
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

    protected $namespaces = [];

    public function onOpen(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $argumentCollection = null): ComponentInterface
    {
        $this->shouldRenderTag = ($arguments['data-namespace-typo3-fluid'] ?? null) === 'true';
        $arguments = $argumentCollection->readAll();
        $resolver = $renderingContext->getViewHelperResolver();
        foreach ($arguments as $name => $value) {
            $parts = explode(':', $name);
            if ($parts[0] === 'xmlns' && isset($parts[1]) && strncmp('http://typo3.org/ns/', $value, 20) === 0) {
                $namespace = $resolver->resolvePhpNamespaceFromFluidNamespace($value);
                $resolver->addNamespace($parts[1], $namespace);
                $this->namespaces[$parts[1]][] = $namespace;
                unset($arguments[$name]);
            }
        }
        return $this;
    }

    public function onClose(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $resolver = $renderingContext->getViewHelperResolver();
        foreach ($this->namespaces as $identifier => $namespaces) {
            foreach ($namespaces as $namespace) {
                $resolver->removeNamespace($identifier, $namespace);
            }
        }
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
