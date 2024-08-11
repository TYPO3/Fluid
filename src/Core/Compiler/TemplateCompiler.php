<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * @internal Nobody should need to override this class.
 * @todo: declare final with next major. Nobody should extend / override
 *        here since compile details can be done in nodes or single VHs.
 */
class TemplateCompiler
{
    public const MODE_NORMAL = 'normal';
    public const MODE_WARMUP = 'warmup';

    protected array $syntaxTreeInstanceCache = [];

    protected RenderingContextInterface $renderingContext;

    protected string $mode = self::MODE_NORMAL;

    protected ?ParsedTemplateInterface $currentlyProcessingState = null;

    private int $variableCounter = 0;

    /**
     * Instruct the TemplateCompiler to enter warmup mode, assigning
     * additional context allowing cache-related implementations to
     * subsequently check the mode.
     *
     * Cannot be reversed once done - should only be used from within
     * FluidCacheWarmerInterface implementations!
     */
    public function enterWarmupMode(): void
    {
        $this->mode = static::MODE_WARMUP;
    }

    /**
     * Returns true only if the TemplateCompiler is in warmup mode.
     */
    public function isWarmupMode(): bool
    {
        return $this->mode === static::MODE_WARMUP;
    }

    public function getCurrentlyProcessingState(): ?ParsedTemplateInterface
    {
        return $this->currentlyProcessingState;
    }

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    public function getRenderingContext(): RenderingContextInterface
    {
        return $this->renderingContext;
    }

    public function disable(): void
    {
        throw new StopCompilingException('Compiling stopped');
    }

    public function isDisabled(): bool
    {
        return !$this->renderingContext->isCacheEnabled();
    }

    public function has(string $identifier): bool
    {
        $identifier = $this->sanitizeIdentifier($identifier);

        if (isset($this->syntaxTreeInstanceCache[$identifier]) || class_exists($identifier, false)) {
            return true;
        }
        if (!$this->renderingContext->isCacheEnabled()) {
            return false;
        }
        if (!empty($identifier)) {
            return (bool)$this->renderingContext->getCache()->get($identifier);
        }
        return false;
    }

    public function get(string $identifier): ParsedTemplateInterface|UncompilableTemplateInterface
    {
        $identifier = $this->sanitizeIdentifier($identifier);

        if (!isset($this->syntaxTreeInstanceCache[$identifier])) {
            if (!class_exists($identifier, false)) {
                $this->renderingContext->getCache()->get($identifier);
            }
            if (!is_a($identifier, UncompilableTemplateInterface::class, true)) {
                $this->syntaxTreeInstanceCache[$identifier] = new $identifier();
            } else {
                return new $identifier();
            }
        }

        return $this->syntaxTreeInstanceCache[$identifier];
    }

    /**
     * Resets the currently processing state
     */
    public function reset(): void
    {
        $this->currentlyProcessingState = null;
    }

    public function store(string $identifier, ParsingState $parsingState): ?string
    {
        if ($this->isDisabled()) {
            $parsingState->setCompilable(false);
            return null;
        }

        $identifier = $this->sanitizeIdentifier($identifier);
        $cache = $this->renderingContext->getCache();
        if (!$parsingState->isCompilable()) {
            $templateCode = '<?php' . PHP_EOL . 'class ' . $identifier .
                ' extends \TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate' . PHP_EOL .
                ' implements \TYPO3Fluid\Fluid\Core\Compiler\UncompilableTemplateInterface' . PHP_EOL .
                '{' . PHP_EOL . '}';
            $cache->set($identifier, $templateCode);
            return $templateCode;
        }

        $this->currentlyProcessingState = $parsingState;
        $this->variableCounter = 0;

        $generatedRenderFunctions = $this->generateSectionCodeFromParsingState($parsingState);
        $generatedRenderFunctions .= $this->generateCodeForSection(
            // @todo: This is weird. $parsingState->getRootNode() is not always a RootNode
            //        since it is type hinted to NodeInterface only?! If it would be a
            //        RootNode, we could just call $parsingState->getRootNode()->compile().
            $this->convertSubNodes($parsingState->getRootNode()->getChildNodes()),
            'render',
            'Main Render function',
        );

        $storedLayoutName = $parsingState->getVariableContainer()->get('layoutName');
        $templateCode = sprintf(
            '<?php' . chr(10) .
            '%s {' . chr(10) .
            '    public function getLayoutName(\\TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface $renderingContext): ?string {' . chr(10) .
            '        %s;' . chr(10) .
            '    }' . chr(10) .
            '    public function hasLayout(): bool {' . chr(10) .
            '        return %s;' . chr(10) .
            '    }' . chr(10) .
            '    public function addCompiledNamespaces(\TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface $renderingContext): void {' . chr(10) .
            '        $renderingContext->getViewHelperResolver()->addNamespaces(%s);' . chr(10) .
            '    }' . chr(10) .
            '    %s' . chr(10) .
            '}' . chr(10),
            'class ' . $identifier . ' extends \TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate',
            $this->generateCodeForLayoutName($storedLayoutName),
            ($parsingState->hasLayout() ? 'true' : 'false'),
            var_export($this->renderingContext->getViewHelperResolver()->getNamespaces(), true),
            $generatedRenderFunctions,
        );
        $this->renderingContext->getCache()->set($identifier, $templateCode);
        return $templateCode;
    }

    /**
     * @todo this type is crazy, this should really be something like NodeInterface|string
     */
    protected function generateCodeForLayoutName(NodeInterface|string|int|float|null|bool $storedLayoutNameArgument): string
    {
        if ($storedLayoutNameArgument instanceof RootNode) {
            $convertedCode = $storedLayoutNameArgument->convert($this);
            $initialization = $convertedCode['initialization'];
            $execution = $convertedCode['execution'];
            return $initialization . chr(10) . 'return ' . $execution;
        }
        return 'return (string)\'' . $storedLayoutNameArgument . '\'';
    }

    protected function generateSectionCodeFromParsingState(ParsingState $parsingState): string
    {
        $generatedRenderFunctions = '';
        if ($parsingState->getVariableContainer()->exists('1457379500_sections')) {
            // @todo: refactor to $parsedTemplate->getSections()
            $sections = $parsingState->getVariableContainer()->get('1457379500_sections');
            foreach ($sections as $sectionName => $sectionRootNode) {
                $generatedRenderFunctions .= $this->generateCodeForSection(
                    // @todo: Verify this is *always* an instance of RootNode
                    //        and call $node->convert($this) directly.
                    $this->convertSubNodes($sectionRootNode->getChildNodes()),
                    'section_' . hash('xxh3', $sectionName),
                    'section ' . $sectionName,
                );
            }
        }
        return $generatedRenderFunctions;
    }

    /**
     * Replaces special characters by underscores
     * @see http://www.php.net/manual/en/language.variables.basics.php
     *
     * @return string the sanitized identifier
     */
    protected function sanitizeIdentifier(string $identifier): string
    {
        return (string)preg_replace('([^a-zA-Z0-9_\x7f-\xff])', '_', $identifier);
    }

    protected function generateCodeForSection(array $converted, string $methodName, string $comment): string
    {
        $initialization = $converted['initialization'];
        $execution = $converted['execution'];
        if ($initialization === '') {
            // Very minor code optimization when $converted['initialization'] is empty.
            // No real benefit, just removes a couple of empty lines.
            return sprintf(
                '/**' . chr(10) .
                ' * %s' . chr(10) .
                ' */' . chr(10) .
                'public function %s(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext): mixed {' . chr(10) .
                '    return %s;' . chr(10) .
                '}' . chr(10),
                $comment,
                $methodName,
                $execution,
            );
        }
        return sprintf(
            '/**' . chr(10) .
            ' * %s' . chr(10) .
            ' */' . chr(10) .
            'public function %s(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext): mixed {' . chr(10) .
            '    %s' . chr(10) .
            '    return %s;' . chr(10) .
            '}' . chr(10),
            $comment,
            $methodName,
            $initialization,
            $execution,
        );
    }

    /**
     * Returns a unique variable name by appending a global index to the given prefix
     */
    public function variableName(string $prefix): string
    {
        return '$' . $prefix . $this->variableCounter++;
    }

    public function wrapChildNodesInClosure(NodeInterface $node): string
    {
        $closure = '';
        $closure .= 'function() use ($renderingContext) {' . chr(10);
        $convertedSubNodes = $this->convertSubNodes($node->getChildNodes());
        $closure .= $convertedSubNodes['initialization'];
        $closure .= sprintf('return %s;', $convertedSubNodes['execution']) . chr(10);
        $closure .= '}';
        return $closure;
    }

    /**
     * Wraps one ViewHelper argument evaluation in a closure that can be
     * rendered by passing a rendering context.
     */
    public function wrapViewHelperNodeArgumentEvaluationInClosure(ViewHelperNode $node, string $argumentName): string
    {
        $arguments = $node->getArguments();
        $argument = $arguments[$argumentName];
        $closure = 'function() use ($renderingContext) {' . chr(10);
        $compiled = $argument->convert($this);
        $closure .= $compiled['initialization'] . chr(10);
        $closure .= 'return ' . $compiled['execution'] . ';' . chr(10);
        $closure .= '}';
        return $closure;
    }

    private function convertSubNodes(array $nodes): array
    {
        switch (count($nodes)) {
            case 0:
                return [
                    'initialization' => '',
                    'execution' => 'NULL',
                ];
            case 1:
                $childNode = current($nodes);
                if ($childNode instanceof NodeInterface) {
                    return $childNode->convert($this);
                }
                // @todo: Having no break here does not make sense, does it?
                //        Shouldn't nodes *always* be instance of NodeInterface anyways?
                //        Also, convert() is called on them below in any case, so this
                //        construct can and should be simplified?!
                // no break
            default:
                $outputVariableName = $this->variableName('output');
                $initializationPhpCode = sprintf('%s = \'\';', $outputVariableName) . chr(10);
                foreach ($nodes as $childNode) {
                    $converted = $childNode->convert($this);
                    $initializationPhpCode .= $converted['initialization'] . chr(10);
                    $initializationPhpCode .= sprintf('%s .= %s;', $outputVariableName, $converted['execution']) . chr(10);
                }
                return [
                    'initialization' => $initializationPhpCode,
                    'execution' => $outputVariableName,
                ];
        }
    }
}
