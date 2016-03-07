<?php
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class TemplateCompiler
 */
class TemplateCompiler {

	const SHOULD_GENERATE_VIEWHELPER_INVOCATION = '##should_gen_viewhelper##';

	/**
	 * @var array
	 */
	protected $syntaxTreeInstanceCache = array();

	/**
	 * @var NodeConverter
	 */
	protected $nodeConverter;

	/**
	 * @var RenderingContextInterface
	 */
	protected $renderingContext;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->nodeConverter = new NodeConverter($this);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @return void
	 */
	public function setRenderingContext(RenderingContextInterface $renderingContext) {
		$this->renderingContext = $renderingContext;
	}

	/**
	 * @return RenderingContextInterface
	 */
	public function getRenderingContext() {
		return $this->renderingContext;
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
	 * @return void
	 */
	public function disable() {
		throw new StopCompilingException('Compiling stopped');
	}

	/**
	 * @return boolean
	 */
	public function isDisabled() {
		return !$this->renderingContext->isCacheEnabled();
	}

	/**
	 * @param string $identifier
	 * @return boolean
	 */
	public function has($identifier) {
		if (isset($this->syntaxTreeInstanceCache[$identifier])) {
			return TRUE;
		}
		if (!$this->renderingContext->isCacheEnabled()) {
			return FALSE;
		}
		$identifier = $this->sanitizeIdentifier($identifier);
		return !empty($identifier) && $this->renderingContext->getCache()->get($identifier);
	}

	/**
	 * @param string $identifier
	 * @return ParsedTemplateInterface
	 */
	public function get($identifier) {
		$identifier = $this->sanitizeIdentifier($identifier);

		if (!isset($this->syntaxTreeInstanceCache[$identifier])) {
			$this->renderingContext->getCache()->get($identifier);
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
		if ($this->isDisabled()) {
			if ($this->renderingContext->isCacheEnabled()) {
				// Compiler is disabled but cache is enabled. Flush cache to make sure.
				$this->renderingContext->getCache()->flush($identifier);
			}
			$parsingState->setCompilable(FALSE);
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

		$classDefinition = 'class ' . $identifier . ' extends \TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate';

		$templateCode = <<<EOD
<?php

%s {

public function getLayoutName(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
\$layout = %s;
if (!\$layout) {
\$layout = '%s';
}
return \$layout;
}
public function hasLayout() {
return %s;
}
public function addCompiledNamespaces(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
\$renderingContext->getViewHelperResolver()->addNamespaces(%s);
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
			var_export($this->renderingContext->getViewHelperResolver()->getNamespaces(), TRUE),
			$generatedRenderFunctions);
		$this->renderingContext->getCache()->set($identifier, $templateCode);
	}

	/**
	 * @param ParsingState $parsingState
	 * @return string
	 */
	protected function generateSectionCodeFromParsingState(ParsingState $parsingState) {
		$generatedRenderFunctions = '';
		if ($parsingState->getVariableContainer()->exists('1457379500_sections')) {
			$sections = $parsingState->getVariableContainer()->get('1457379500_sections'); // TODO: refactor to $parsedTemplate->getSections()
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
public function %s(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface \$renderingContext) {
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
				'return \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, %s);',
				$compiledIfArgumentStack['execution']
			) . chr(10);
		} else {
			$closure .= sprintf('$argument = unserialize(\'%s\'); return $argument->evaluate($renderingContext);', serialize($argument)) . chr(10);
		}
		$closure .= '}';
		return $closure;
	}

}
