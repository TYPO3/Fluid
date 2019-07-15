<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
     * @return ParsedTemplateInterface Parsed template
     * @throws Exception
     */
    public function parse(string $templateString, ?Configuration $configuration = null): ParsedTemplateInterface
    {
        $templateString = $this->preProcessTemplateSource($templateString);

        $source = new Source($templateString);
        $contexts = new Contexts();
        $sequencer = new Sequencer(
            $this->renderingContext,
            $this->getParsingState(),
            $contexts,
            $source,
            $configuration ?? $this->configuration
        );
        $parsingState = $sequencer->sequence();

        return $parsingState;
    }

    /**
     * @param string $templateIdentifier
     * @param \Closure $templateSourceClosure Closure which returns the template source if needed
     * @return ParsedTemplateInterface
     */
    public function getOrParseAndStoreTemplate(string $templateIdentifier, \Closure $templateSourceClosure): ParsedTemplateInterface
    {
        return $this->parseTemplateSource($templateIdentifier, $templateSourceClosure);
    }

    /**
     * @param string $templateIdentifier
     * @param \Closure $templateSourceClosure
     * @return ParsedTemplateInterface
     */
    protected function parseTemplateSource(string $templateIdentifier, \Closure $templateSourceClosure): ParsedTemplateInterface
    {
        $parsedTemplate = $this->parse(
            $templateSourceClosure($this, $this->renderingContext->getTemplatePaths()),
            $this->renderingContext->getParserConfiguration()
        );
        $parsedTemplate->setIdentifier($templateIdentifier);
        return $parsedTemplate;
    }

    /**
     * Pre-process the template source, making all registered TemplateProcessors
     * do what they need to do with the template source before it is parsed.
     *
     * @param string $templateSource
     * @return string
     */
    protected function preProcessTemplateSource(string $templateSource): string
    {
        foreach ($this->renderingContext->getTemplateProcessors() as $templateProcessor) {
            $templateSource = $templateProcessor->preProcessSource($templateSource);
        }
        return $templateSource;
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

    /**
     * @return ParsingState
     */
    protected function getParsingState(): ParsingState
    {
        $variableProvider = $this->renderingContext->getVariableProvider();
        $state = new ParsingState();
        $state->setVariableProvider($variableProvider->getScopeCopy($variableProvider->getAll()));
        return $state;
    }
}
