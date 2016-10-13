<?php
namespace TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Patterns;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This template processor takes care of the following things:
 *
 *   - replace cdata sections with empty lines (including nested cdata)
 *   - register/ignore namespaces through xmlns and shorthand syntax
 *   - report any unregistered/unignored namespaces through exception
 *
 */
class NamespaceDetectionTemplateProcessor implements TemplateProcessorInterface
{

    const NAMESPACE_DECLARATION = '/(?<!\\\\){namespace\s*(?P<identifier>[a-zA-Z\*]+[a-zA-Z0-9\.\*]*)\s*(=\s*(?P<phpNamespace>(?:[A-Za-z0-9\.]+|Tx)(?:\\\\\w+)+)\s*)?}/m';

    const SPLIT_PATTERN_TEMPLATE_OPEN_NAMESPACETAG = '/xmlns:([a-z0-9\.]+)=("[^"]+"|\'[^\']+\')*/xi';

    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * @var array()
     */
    protected $localNamespaces = [];

    /**
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext)
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Pre-process the template source before it is
     * returned to the TemplateParser or passed to
     * the next TemplateProcessorInterface instance.
     *
     * @param string $templateSource
     * @return string
     */
    public function preProcessSource($templateSource)
    {
        $templateSource = $this->replaceCdataSectionsByEmptyLines($templateSource);
        $this->registerNamespacesFromTemplateSource($templateSource);
        $this->throwExceptionsForUnhandledNamespaces($templateSource);
        return $templateSource;
    }

    /**
     * Replaces all cdata sections with empty lines to exclude it from further
     * processing in the templateParser while maintaining the line-count
     * of the template string for the exception handler to reference to.
     *
     * @param string $templateSource
     * @return string
     */
    public function replaceCdataSectionsByEmptyLines($templateSource)
    {
        $parts = preg_split('/(\<\!\[CDATA\[|\]\]\>)/', $templateSource, -1, PREG_SPLIT_DELIM_CAPTURE);

        $balance = 0;
        foreach ($parts as $index => $part) {
            if ($part === '<![CDATA[') {
                $balance++;
            }
            if ($balance > 0) {
                $parts[$index] = str_repeat(PHP_EOL, substr_count($part, PHP_EOL));
            }
            if ($part === ']]>') {
                $balance--;
            }
        }

        return implode('', $parts);
    }

    /**
     * Register all namespaces that are declared inside the template string
     *
     * @param string $templateSource
     * @return void
     */
    public function registerNamespacesFromTemplateSource($templateSource)
    {
        $viewHelperResolver = $this->renderingContext->getViewHelperResolver();
        if (preg_match_all(static::SPLIT_PATTERN_TEMPLATE_OPEN_NAMESPACETAG, $templateSource, $matchedVariables, PREG_SET_ORDER) > 0) {
            foreach ($matchedVariables as $namespaceMatch) {
                $viewHelperNamespace = $this->renderingContext->getTemplateParser()->unquoteString($namespaceMatch[2]);
                $phpNamespace = $viewHelperResolver->resolvePhpNamespaceFromFluidNamespace($viewHelperNamespace);
                if (strpos($phpNamespace, '/') === false) {
                    $viewHelperResolver->addNamespace($namespaceMatch[1], $phpNamespace);
                }
            }
        }

        preg_match_all(static::NAMESPACE_DECLARATION, $templateSource, $namespaces);
        foreach ($namespaces['identifier'] as $key => $identifier) {
            $namespace = $namespaces['phpNamespace'][$key];
            if (strlen($namespace) === 0) {
                $namespace = null;
            }
            $viewHelperResolver->addNamespace($identifier, $namespace);
        }
    }

    /**
     * Throw an UnknownNamespaceException for any unknown and not ignored
     * namespace inside the template string
     *
     * @param string $templateSource
     * @return void
     */
    public function throwExceptionsForUnhandledNamespaces($templateSource)
    {
        $viewHelperResolver = $this->renderingContext->getViewHelperResolver();
        $splitTemplate = preg_split(Patterns::$SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS, $templateSource, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($splitTemplate as $templateElement) {
            if (preg_match(Patterns::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG, $templateElement, $matchedVariables) > 0) {
                if (!$viewHelperResolver->isNamespaceValidOrIgnored($matchedVariables['NamespaceIdentifier'])) {
                    throw new UnknownNamespaceException('Unkown Namespace: ' . htmlspecialchars($matchedVariables[0]));
                }
                continue;
            } elseif (preg_match(Patterns::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG, $templateElement, $matchedVariables) > 0) {
                continue;
            }

            $sections = preg_split(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX, $templateElement, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            foreach ($sections as $section) {
                if (preg_match(Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
                    preg_match_all(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER, $section, $shorthandViewHelpers, PREG_SET_ORDER);
                    if (is_array($shorthandViewHelpers) === true) {
                        foreach ($shorthandViewHelpers as $shorthandViewHelper) {
                            if (!$viewHelperResolver->isNamespaceValidOrIgnored($shorthandViewHelper['NamespaceIdentifier'])) {
                                throw new UnknownNamespaceException('Unkown Namespace: ' . $shorthandViewHelper['NamespaceIdentifier']);
                            }
                        }
                    }
                }
            }
        }
    }
}
