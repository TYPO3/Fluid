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
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * HTML container tag ViewHelper
 *
 * Intended for use as aliased ViewHelper so that <html>
 * tags will be handled by this ViewHelper class. Allows
 * Fluid to extract namespaces from <html> tags and if
 * so instructed, not render the <html> tag itself but
 * only the child content.
 */
class HtmlViewHelper extends AbstractViewHelper implements TransparentComponentInterface
{
    protected $escapeOutput = false;

    protected $escapeChildren = true;

    protected $shouldRenderTag = true;

    protected $namespaces = [];

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        $arguments = $this->getArguments()->getArrayCopy();
        $resolver = $renderingContext->getViewHelperResolver();
        foreach ($this->arguments as $name => $value) {
            if ($name === 'data-namespace-typo3-fluid') {
                $this->shouldRenderTag = $value !== 'true';
                $this->arguments['data-namespace-typo3-fluid'] = null;
                continue;
            }

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
        return $this;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $content = $this->evaluateChildren($renderingContext);
        if (!$this->shouldRenderTag) {
            return $content;
        }

        $tagBuilder = new TagBuilder('html');
        $tagBuilder->ignoreEmptyAttributes(true);
        $tagBuilder->addAttributes($this->arguments->setRenderingContext($renderingContext)->getArrayCopy());
        $tagBuilder->setContent($content);
        return $tagBuilder->render();
    }

    public function allowUndeclaredArgument(string $argumentName): bool
    {
        return true;
    }
}
