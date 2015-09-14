<?php
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\VariableExtractor;

/**
 * Class NodeConverter
 */
class NodeConverter {

	/**
	 * @var integer
	 */
	protected $variableCounter = 0;

	/**
	 * @var TemplateCompiler
	 */
	protected $templateCompiler;

	/**
	 * @param TemplateCompiler $templateCompiler
	 */
	public function __construct(TemplateCompiler $templateCompiler) {
		$this->templateCompiler = $templateCompiler;
	}

	/**
	 * @param integer $variableCounter
	 * @return void
	 */
	public function setVariableCounter($variableCounter) {
		$this->variableCounter = $variableCounter;
	}

	/**
	 * Returns an array with two elements:
	 * - initialization: contains PHP code which is inserted *before* the actual rendering call. Must be valid, i.e. end with semi-colon.
	 * - execution: contains *a single PHP instruction* which needs to return the rendered output of the given element. Should NOT end with semi-colon.
	 *
	 * @param NodeInterface $node
	 * @return array two-element array, see above
	 * @throws FluidException
	 */
	public function convert(NodeInterface $node) {
		$converted = array(
			'initialization' => '// Uncompilable/convertible node type: ' . get_class($node) . chr(10),
			'execution' => ''
		);
		if ($node instanceof TextNode) {
			$converted = $this->convertTextNode($node);
		} elseif ($node instanceof ExpressionNodeInterface) {
			$converted = $this->convertExpressionNode($node);
		} elseif ($node instanceof NumericNode) {
			$converted = $this->convertNumericNode($node);
		} elseif ($node instanceof ViewHelperNode) {
			$converted = $this->convertViewHelperNode($node);
		} elseif ($node instanceof ObjectAccessorNode) {
			$converted = $this->convertObjectAccessorNode($node);
		} elseif ($node instanceof ArrayNode) {
			$converted = $this->convertArrayNode($node);
		} elseif ($node instanceof RootNode) {
			$converted = $this->convertListOfSubNodes($node);
		} elseif ($node instanceof BooleanNode) {
			$converted = $this->convertBooleanNode($node);
		}
		return $converted;
	}

	/**
	 * @param TextNode $node
	 * @return array
	 * @see convert()
	 */
	protected function convertTextNode(TextNode $node) {
		return array(
			'initialization' => '',
			'execution' => '\'' . $this->escapeTextForUseInSingleQuotes($node->getText()) . '\''
		);
	}

	/**
	 * @param NumericNode $node
	 * @return array
	 * @see convert()
	 */
	protected function convertNumericNode(NumericNode $node) {
		return array(
			'initialization' => '',
			'execution' => $node->getValue()
		);
	}

	/**
	 * Convert a single ViewHelperNode into its cached representation. If the ViewHelper implements the "Compilable" facet,
	 * the ViewHelper itself is asked for its cached PHP code representation. If not, a ViewHelper is built and then invoked.
	 *
	 * @param ViewHelperNode $node
	 * @return array
	 * @see convert()
	 */
	protected function convertViewHelperNode(ViewHelperNode $node) {
		$initializationPhpCode = '// Rendering ViewHelper ' . $node->getViewHelperClassName() . chr(10);

		// Build up $arguments array
		$argumentsVariableName = $this->variableName('arguments');
		$initializationPhpCode .= sprintf('%s = array();', $argumentsVariableName) . chr(10);

		$alreadyBuiltArguments = array();
		foreach ($node->getArguments() as $argumentName => $argumentValue) {
			if ($argumentValue instanceof NodeInterface) {
				$converted = $this->convert($argumentValue);
			} else {
				$converted = array(
					'initialization' => '',
					'execution' => $argumentValue
				);
			}
			$initializationPhpCode .= $converted['initialization'];
			$initializationPhpCode .= sprintf(
				'%s[\'%s\'] = %s;',
				$argumentsVariableName,
				$argumentName,
				$converted['execution']
			) . chr(10);
			$alreadyBuiltArguments[$argumentName] = TRUE;
		}

		$arguments = $node->getArgumentDefinitions();
		foreach ($arguments  as $argumentName => $argumentDefinition) {
			if (!isset($alreadyBuiltArguments[$argumentName])) {
				$initializationPhpCode .= sprintf(
						'%s[\'%s\'] = %s;',
						$argumentsVariableName,
						$argumentName,
						var_export($argumentDefinition->getDefaultValue(), TRUE)
					) . chr(10);
			}
		}

		// Build up closure which renders the child nodes
		$renderChildrenClosureVariableName = $this->variableName('renderChildrenClosure');
		$initializationPhpCode .= sprintf(
				'%s = %s;',
				$renderChildrenClosureVariableName,
				$this->templateCompiler->wrapChildNodesInClosure($node)
			) . chr(10);

		$viewHelperInitializationPhpCode = '';
		$convertedViewHelperExecutionCode = $node->getUninitializedViewHelper()->compile(
			$argumentsVariableName,
			$renderChildrenClosureVariableName,
			$viewHelperInitializationPhpCode,
			$node,
			$this->templateCompiler
		);
		$initializationPhpCode .= $viewHelperInitializationPhpCode;
		$initializationArray = array(
			'initialization' => $initializationPhpCode,
			'execution' => $convertedViewHelperExecutionCode === NULL ? 'NULL' : $convertedViewHelperExecutionCode
		);
		return $initializationArray;
	}

	/**
	 * @param ObjectAccessorNode $node
	 * @return array
	 * @see convert()
	 */
	protected function convertObjectAccessorNode(ObjectAccessorNode $node) {
		$arrayVariableName = $this->variableName('array');
		$accessors = $node->getAccessors();
		$providerReference = '$renderingContext->getVariableProvider()';
		$path = $node->getObjectPath();
		$pathSegments = explode('.', $path);
		if ($path === '_all') {
			return array(
				'initialization' => '',
				'execution' => sprintf('%s->getAll()', $providerReference)
			);
		} elseif (
			1 === count(array_unique($accessors))
			&& reset($accessors) === VariableExtractor::ACCESSOR_ARRAY
			&& count($accessors) === count($pathSegments)
			&& FALSE === strpos($path, '{')
		) {
			// every extractor used in this path is a straight-forward arrayaccess.
			// Create the compiled code as a plain old variable assignment:
			return array(
				'initialization' => '',
				'execution' => sprintf(
					'%s[\'%s\']',
					$providerReference,
					str_replace('.', '\'][\'', $path)
				)
			);
		}
		$accessorsVariable = var_export($accessors, TRUE);
		$initialization = sprintf('%s = %s;', $arrayVariableName, $accessorsVariable);
		return array(
			'initialization' => $initialization,
			'execution' => sprintf(
				'%s->getByPath(\'%s\', %s)',
				$providerReference,
				$path,
				$arrayVariableName
			)
		);
	}

	/**
	 * @param ArrayNode $node
	 * @return array
	 * @see convert()
	 */
	protected function convertArrayNode(ArrayNode $node) {
		$initializationPhpCode = '// Rendering Array' . chr(10);
		$arrayVariableName = $this->variableName('array');

		$initializationPhpCode .= sprintf('%s = array();', $arrayVariableName) . chr(10);

		foreach ($node->getInternalArray() as $key => $value) {
			if ($value instanceof NodeInterface) {
				$converted = $this->convert($value);
				$initializationPhpCode .= $converted['initialization'];
				$initializationPhpCode .= sprintf(
						'%s[\'%s\'] = %s;',
						$arrayVariableName,
						$key,
						$converted['execution']
					) . chr(10);
			} elseif (is_numeric($value)) {
				// this case might happen for simple values
				$initializationPhpCode .= sprintf(
						'%s[\'%s\'] = %s;',
						$arrayVariableName,
						$key,
						$value
					) . chr(10);
			} else {
				// this case might happen for simple values
				$initializationPhpCode .= sprintf(
						'%s[\'%s\'] = \'%s\';',
						$arrayVariableName,
						$key,
						$this->escapeTextForUseInSingleQuotes($value)
					) . chr(10);
			}
		}
		return array(
			'initialization' => $initializationPhpCode,
			'execution' => $arrayVariableName
		);
	}

	/**
	 * @param NodeInterface $node
	 * @return array
	 * @see convert()
	 */
	public function convertListOfSubNodes(NodeInterface $node) {
		switch (count($node->getChildNodes())) {
			case 0:
				return array(
					'initialization' => '',
					'execution' => 'NULL'
				);
			case 1:
				return $this->convert(current($node->getChildNodes()));
			default:
				$outputVariableName = $this->variableName('output');
				$initializationPhpCode = sprintf('%s = \'\';', $outputVariableName) . chr(10);

				foreach ($node->getChildNodes() as $childNode) {
					$converted = $this->convert($childNode);

					$initializationPhpCode .= $converted['initialization'] . chr(10);
					$initializationPhpCode .= sprintf('%s .= %s;', $outputVariableName, $converted['execution']) . chr(10);
				}

				return array(
					'initialization' => $initializationPhpCode,
					'execution' => $outputVariableName
				);
		}
	}

	/**
	 * @param ExpressionNodeInterface $node
	 * @return array
	 * @see convert()
	 */
	protected function convertExpressionNode(ExpressionNodeInterface $node) {
		$handlerClass = get_class($node);
		$expressionVariable = $this->variableName('string');
		$matchesVariable = $this->variableName('array');
		$initializationPhpCode = sprintf('// Rendering %s node' . chr(10), $handlerClass);
		$initializationPhpCode .= sprintf('%s = \'%s\';' , $expressionVariable, $node->getExpression()) . chr(10);
		$initializationPhpCode .= sprintf('%s = %s;' , $matchesVariable, var_export($node->getMatches(), TRUE)) . chr(10);
		return array(
			'initialization' => $initializationPhpCode,
			'execution' => sprintf(
				'\%s::evaluateExpression($renderingContext, %s, %s)',
				$handlerClass,
				$expressionVariable,
				$matchesVariable
			)
		);
	}

	/**
	 * @param BooleanNode $node
	 * @return array
	 * @see convert()
	 */
	protected function convertBooleanNode(BooleanNode $node) {
		$stack = $this->convertArrayNode(new ArrayNode($node->getStack()));
		$initializationPhpCode = '// Rendering Boolean node' . chr(10);
		$initializationPhpCode = $stack['initialization'] . chr(10);
		return array(
			'initialization' => $initializationPhpCode,
			'execution' => sprintf(
				'\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, %s)',
				$stack['execution']
			)
		);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function escapeTextForUseInSingleQuotes($text) {
		return str_replace(array('\\', '\''), array('\\\\', '\\\''), $text);
	}

	/**
	 * Returns a unique variable name by appending a global index to the given prefix
	 *
	 * @param string $prefix
	 * @return string
	 */
	public function variableName($prefix) {
		return '$' . $prefix . $this->variableCounter++;
	}

}
