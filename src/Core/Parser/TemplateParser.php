<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Template parser building up an object syntax tree
 */
class TemplateParser
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * @param RenderingContextInterface $renderingContext
     * @return self
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext): TemplateParser
    {
        $this->renderingContext = $renderingContext;
        $this->configuration = $renderingContext->getParserConfiguration();
        return $this;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Parses a given template string and returns a parsed template object.
     *
     * The resulting ParsedTemplate can then be rendered by calling evaluate() on it.
     *
     * Normally, you should use a subclass of AbstractTemplateView instead of calling the
     * TemplateParser directly.
     *
     * @param string $templateString The template to parse as a string
     * @param Configuration|null Template parsing configuration to use
     * @return ComponentInterface Parsed template
     * @throws Exception
     */
    public function parse(string $templateString, ?Configuration $configuration = null): ComponentInterface
    {
        static $cache = [];
        $checksum = sha1($templateString) . (string) ($configuration ?? $this->configuration)->isFeatureEnabled(Configuration::FEATURE_ESCAPING);
        if (!isset($cache[$checksum])) {
            $source = new Source($templateString);
            $contexts = new Contexts();
            $sequencer = new Sequencer(
                $this->renderingContext,
                $contexts,
                $source,
                $configuration ?? $this->configuration
            );
            $cache[$checksum] = $sequencer->sequence();
        }
        return $cache[$checksum];
    }

    /**
     * @param string $templateIdentifier
     * @param \Closure $templateSourceClosure Closure which returns the template source if needed
     * @return ComponentInterface
     */
    public function getOrParseAndStoreTemplate(string $templateIdentifier, \Closure $templateSourceClosure): ComponentInterface
    {
        return $this->parseTemplateSource($templateIdentifier, $templateSourceClosure);
    }

    /**
     * @param string $templateIdentifier
     * @param \Closure $templateSourceClosure
     * @return ComponentInterface
     */
    protected function parseTemplateSource(string $templateIdentifier, \Closure $templateSourceClosure): ComponentInterface
    {
        $parsedTemplate = $this->parse(
            $templateSourceClosure($this, $this->renderingContext->getTemplatePaths()),
            $this->renderingContext->getParserConfiguration()
        );
        //$parsedTemplate->setIdentifier($templateIdentifier);
        return $parsedTemplate;
    }

    /**
     * Removes escaping from a given argument string and trims the outermost
     * quotes.
     *
     * This method is meant as a helper for regular expression results.
     *
     * @param string $quotedValue Value to unquote
     * @return string Unquoted value
     */
    public function unquoteString(string $quotedValue): string
    {
        $value = $quotedValue;
        if ($value === '') {
            return $value;
        }
        if ($quotedValue{0} === '"') {
            $value = str_replace('\\"', '"', preg_replace('/(^"|"$)/', '', $quotedValue));
        } elseif ($quotedValue{0} === '\'') {
            $value = str_replace("\\'", "'", preg_replace('/(^\'|\'$)/', '', $quotedValue));
        }
        return str_replace('\\\\', '\\', $value);
    }
}
