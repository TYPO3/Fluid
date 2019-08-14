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
     * @var array 
     */
    protected $stack = [];

    public function __construct(RenderingContextInterface $renderingContext)
    {
        $this->renderingContext = $renderingContext;
        $this->configuration = $renderingContext->getParserConfiguration();
    }

    public function parseFile(string $templatePathAndFilename, ?Configuration $configuration = null): ComponentInterface
    {
        $hash = sha1_file($templatePathAndFilename);
        if (isset($this->stack[$hash])) {
            return $this->stack[$hash];
        }
        return $this->getOrParseAndStoreTemplate(
            $this->createIdentifierForFile($templatePathAndFilename, ''),
            function () use ($templatePathAndFilename): string {
                return file_get_contents($templatePathAndFilename);
            }
        );
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
        $hash = sha1($templateString);
        $source = new Source($templateString);
        $contexts = new Contexts();
        $sequencer = new Sequencer(
            $this->renderingContext,
            $contexts,
            $source,
            $configuration ?? $this->configuration
        );
        // Recursion support: triggering parsing of the same source file from within the file returns a reference
        // to the still unfinished EntryNode created by the Sequencer. The returned instance still does not have all
        // child nodes until the Sequencer has finished. The second time the template is parsed the temporary EntryNode
        // is returned to prevent infinite recursion.
        // The first instance now contains a circular reference to itself, as a branch nested in children somewhere.
        // Any subsequent usages of the same source creates additional references to the original/root/parent.
        $this->stack[$hash] = $sequencer->getComponent();
        $component = $sequencer->sequence();
        return $component;
    }

    /**
     * @param string $templateIdentifier
     * @param \Closure $templateSourceClosure Closure which returns the template source if needed
     * @return ComponentInterface
     */
    public function getOrParseAndStoreTemplate(string $templateIdentifier, \Closure $templateSourceClosure): ComponentInterface
    {
        if (!$this->configuration->isFeatureEnabled(Configuration::FEATURE_RUNTIME_CACHE)) {
            return $this->parseTemplateSource($templateIdentifier, $templateSourceClosure);
        }
        static $cache = [];
        if (!isset($cache[$templateIdentifier])) {
            $cache[$templateIdentifier] = $this->parseTemplateSource($templateIdentifier, $templateSourceClosure);
        }
        return $cache[$templateIdentifier];
    }

    /**
     * @param string $templateIdentifier
     * @param \Closure $templateSourceClosure
     * @return ComponentInterface
     */
    protected function parseTemplateSource(string $templateIdentifier, \Closure $templateSourceClosure): ComponentInterface
    {
        $parsedTemplate = $this->parse(
            $templateSourceClosure($this->renderingContext),
            $this->renderingContext->getParserConfiguration()
        );
        //$parsedTemplate->setIdentifier($templateIdentifier);
        return $parsedTemplate;
    }

    /**
     * Returns a unique identifier for the given file in the format
     * <PackageKey>_<SubPackageKey>_<ControllerName>_<prefix>_<SHA1>
     * The SH1 hash is a checksum that is based on the file path and last modification date
     *
     * @param string $pathAndFilename
     * @param string $prefix
     * @return string
     */
    protected function createIdentifierForFile(string $pathAndFilename, string $prefix): string
    {
        return sprintf('%s_%s', $prefix, sha1($pathAndFilename));
    }
}
