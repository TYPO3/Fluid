<?php
namespace TYPO3\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3\Fluid\Core\Parser\ParsingState;
use TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractExpressionNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\MathExpressionNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TernaryExpressionNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Class TemplateCompiler
 */
class TemplateCompiler {

	const SHOULD_GENERATE_VIEWHELPER_INVOCATION = '##should_gen_viewhelper##';

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
	public function setTemplateCache(FluidCacheInterface $templateCache) {
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

		$identifier = $this->sanitizeIdentifier($identifier);
		$this->nodeConverter->setVariableCounter(0);
		$generatedRenderFunctions = $this->generateSectionCodeFromParsingState($parsingState);

		$generatedRenderFunctions .= $this->generateCodeForSection(
			$this->nodeConverter->convertListOfSubNodes($parsingState->getRootNode()),
			'render',
			'Main Render function'
		);

		$convertedLayoutNameNode = $parsingState->hasLayout() ? $this->nodeConverter->convert(
			$parsingState->getLayoutNameNode()) : array('initialization' => '',
			'execution' => 'NULL'
		);

		$classDefinition = 'class ' . $identifier . ' extends \TYPO3\Fluid\Core\Compiler\AbstractCompiledTemplate';

		$templateCode = <<<EOD
<?php

%s {

public function getVariableContainer() {
	// TODO
	return new \TYPO3\Fluid\Core\Variables\StandardVariableProvider();
}
public function getLayoutName(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
\$self = \$this;
%s
return %s;
}
public function hasLayout() {
return %s;
}

%s

}
EOD;
		$templateCode = sprintf($templateCode,
				$classDefinition,
				$convertedLayoutNameNode['initialization'],
				$convertedLayoutNameNode['execution'],
				($parsingState->hasLayout() ? 'TRUE' : 'FALSE'),
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
public function %s(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
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
		$closure .= sprintf('$argument = unserialize(\'%s\'); return $argument->evaluate($renderingContext);', serialize($argument)) . chr(10);
		$closure .= '}';
		return $closure;
	}

}
