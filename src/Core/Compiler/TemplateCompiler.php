<?php
namespace NamelessCoder\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Cache\FluidCacheInterface;
use NamelessCoder\Fluid\Core\Parser\ParsedTemplateInterface;
use NamelessCoder\Fluid\Core\Parser\ParsingState;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\AbstractExpressionNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\MathExpressionNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\TernaryExpressionNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Class TemplateCompiler
 */
class TemplateCompiler {

	const SHOULD_GENERATE_VIEWHELPER_INVOCATION = '##should_gen_viewhelper##';

	/**
	 * @var boolean
	 */
	protected $disabled = FALSE;

	/**
	 * @var FluidCacheInterface
	 */
	protected $templateCache = NULL;

	/**
	 * @var array
	 */
	protected $syntaxTreeInstanceCache = array();

	/**
	 * @var ViewHelperResolver
	 */
	protected $viewHelperResolver;

	/**
	 * @var NodeConverter
	 */
	protected $nodeConverter;

	/**
	 * Constructor
	 */
	public function __construct(ViewHelperResolver $viewHelperResolver = NULL) {
		if (!$viewHelperResolver) {
			$viewHelperResolver = new ViewHelperResolver();
		}
		$this->viewHelperResolver = $viewHelperResolver;
		$this->nodeConverter = new NodeConverter($this);
	}

	/**
	 * @return void
	 */
	public function disable() {
		$this->disabled = TRUE;
	}

	/**
	 * @return boolean
	 */
	public function isDisabled() {
		return $this->disabled;
	}

	/**
	 * @param ViewHelperResolver $viewHelperResolver
	 * @return void
	 */
	public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver) {
		$this->viewHelperResolver = $viewHelperResolver;
	}

	/**
	 * @param FluidCacheInterface $templateCache
	 * @return void
	 */
	public function setTemplateCache(FluidCacheInterface $templateCache = NULL) {
		$this->templateCache = $templateCache;
	}

	/**
	 * @param NodeConverter $nodeConverter
	 * @return void
	 */
	public function setNodeConverter(NodeConverter $nodeConverter) {
		$this->nodeConverter = $nodeConverter;
	}

	/**
	 * @return NodeConverter
	 */
	public function getNodeConverter() {
		return $this->nodeConverter;
	}

	/**
	 * @param string $identifier
	 * @return boolean
	 */
	public function has($identifier) {
		if (!$this->templateCache instanceof FluidCacheInterface) {
			return FALSE;
		}
		$identifier = $this->sanitizeIdentifier($identifier);
		return !empty($identifier) && $this->templateCache->get($identifier);
	}

	/**
	 * @param string $identifier
	 * @return ParsedTemplateInterface
	 */
	public function get($identifier) {
		$identifier = $this->sanitizeIdentifier($identifier);
		if (!isset($this->syntaxTreeInstanceCache[$identifier])) {
			$this->templateCache->get($identifier);
			$this->syntaxTreeInstanceCache[$identifier] = new $identifier();
		}

		return $this->syntaxTreeInstanceCache[$identifier];
	}

	/**
	 * @param string $identifier
	 * @param ParsingState $parsingState
	 * @return void
	 */
	public function store($identifier, ParsingState $parsingState) {
		if (!$this->templateCache instanceof FluidCacheInterface) {
			return;
		}
		if ($this->disabled) {
			$this->templateCache->flush($identifier);
			return;
		}

		$identifier = $this->sanitizeIdentifier($identifier);
		$this->nodeConverter->setVariableCounter(0);
		$generatedRenderFunctions = $this->generateSectionCodeFromParsingState($parsingState);

		$generatedRenderFunctions .= $this->generateCodeForSection(
			$this->nodeConverter->convertListOfSubNodes($parsingState->getRootNode()),
			'render',
			'Main Render function'
		);

		$classDefinition = 'class ' . $identifier . ' extends \NamelessCoder\Fluid\Core\Compiler\AbstractCompiledTemplate';

		$templateCode = <<<EOD
<?php

%s {

public function getLayoutName(\NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
\$layout = %s;
if (!\$layout) {
\$layout = '%s';
}
return \$layout;
}
public function hasLayout() {
return %s;
}
public function addCompiledNamespaces(\NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
\$namespaces = %s;
\$resolver = \$renderingContext->getViewHelperResolver();
foreach (\$namespaces as \$namespace => \$phpNamespace) {
\$resolver->registerNamespace(\$namespace, \$phpNamespace);
}
}

%s

}
EOD;
		$templateCode = sprintf(
			$templateCode,
			$classDefinition,
			'$renderingContext->getVariableProvider()->get(\'layoutName\')',
			$parsingState->getVariableContainer()->get('layoutName'),
			($parsingState->hasLayout() ? 'TRUE' : 'FALSE'),
			var_export($parsingState->getViewHelperResolver()->getNamespaces(), TRUE),
			$generatedRenderFunctions);
		$this->templateCache->set($identifier, $templateCode);
	}

	/**
	 * @param ParsingState $parsingState
	 * @return string
	 */
	protected function generateSectionCodeFromParsingState(ParsingState $parsingState) {
		$generatedRenderFunctions = '';
		if ($parsingState->getVariableContainer()->exists('sections')) {
			$sections = $parsingState->getVariableContainer()->get('sections'); // TODO: refactor to $parsedTemplate->getSections()
			foreach ($sections as $sectionName => $sectionRootNode) {
				$generatedRenderFunctions .= $this->generateCodeForSection(
					$this->nodeConverter->convertListOfSubNodes($sectionRootNode),
					'section_' . sha1($sectionName),
					'section ' . $sectionName
				);
			}
		}
		return $generatedRenderFunctions;
	}

	/**
	 * Replaces special characters by underscores
	 * @see http://www.php.net/manual/en/language.variables.basics.php
	 *
	 * @param string $identifier
	 * @return string the sanitized identifier
	 */
	protected function sanitizeIdentifier($identifier) {
		return preg_replace('([^a-zA-Z0-9_\x7f-\xff])', '_', $identifier);
	}

	/**
	 * @param array $converted
	 * @param string $expectedFunctionName
	 * @param string $comment
	 * @return string
	 */
	protected function generateCodeForSection(array $converted, $expectedFunctionName, $comment) {
		$templateCode = <<<EOD
/**
 * %s
 */
public function %s(\NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
\$self = \$this;
%s
return %s;
}

EOD;
		return sprintf($templateCode, $comment, $expectedFunctionName, $converted['initialization'], $converted['execution']);
	}

	/**
	 * Returns a unique variable name by appending a global index to the given prefix
	 *
	 * @param string $prefix
	 * @return string
	 */
	public function variableName($prefix) {
		return $this->nodeConverter->variableName($prefix);
	}

	/**
	 * @param NodeInterface $node
	 * @return string
	 */
	public function wrapChildNodesInClosure(NodeInterface $node) {
		$closure = '';
		$closure .= 'function() use ($renderingContext, $self) {' . chr(10);
		$convertedSubNodes = $this->nodeConverter->convertListOfSubNodes($node);
		$closure .= $convertedSubNodes['initialization'];
		$closure .= sprintf('return %s;', $convertedSubNodes['execution']) . chr(10);
		$closure .= '}';
		return $closure;
	}

	/**
	 * Wraps one ViewHelper argument evaluation in a closure that can be
	 * rendered by passing a rendering context.
	 *
	 * @param ViewHelperNode $node
	 * @param string $argumentName
	 * @return string
	 */
	public function wrapViewHelperNodeArgumentEvaluationInClosure(ViewHelperNode $node, $argumentName) {
		$arguments = $node->getArguments();
		$argument = $arguments[$argumentName];
		$closure = 'function() use ($renderingContext, $self) {' . chr(10);
		if ($node->getArgumentDefinition($argumentName)->getType() === 'boolean') {
			// We treat boolean nodes by compiling a closure to evaluate the stack of the boolean argument
			$compiledIfArgumentStack = $this->nodeConverter->convert(new ArrayNode($argument->getStack()));
			$closure .= $compiledIfArgumentStack['initialization'] . chr(10);
			$closure .= sprintf(
				'return \NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, %s);',
				$compiledIfArgumentStack['execution']
			) . chr(10);
		} else {
			$closure .= sprintf('$argument = unserialize(\'%s\'); return $argument->evaluate($renderingContext);', serialize($argument)) . chr(10);
		}
		$closure .= '}';
		return $closure;
	}

}
