<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Base test class for testing view helpers
 */
abstract class ViewHelperBaseTestcase extends UnitTestCase {

	/**
	 * @var ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * Mock contents of the $viewHelperVariableContainer in the format:
	 * array(
	 *  'Some\ViewHelper\Class' => array('key1' => 'value1', 'key2' => 'value2')
	 * )
	 *
	 * @var array
	 */
	protected $viewHelperVariableContainerData = array();

	/**
	 * @var TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * @var TagBuilder
	 */
	protected $tagBuilder;

	/**
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * @var RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->viewHelperVariableContainer = new ViewHelperVariableContainer();
		$this->templateVariableContainer = new StandardVariableProvider();
		$this->renderingContext = new RenderingContext();
		$this->tagBuilder = new TagBuilder();
		$this->renderingContext->setVariableProvider($this->templateVariableContainer);
		$this->renderingContext->injectViewHelperVariableContainer($this->viewHelperVariableContainer);
	}

	/**
	 * @param string $viewHelperName
	 * @param string $key
	 * @return boolean
	 */
	public function viewHelperVariableContainerExistsCallback($viewHelperName, $key) {
		return isset($this->viewHelperVariableContainerData[$viewHelperName][$key]);
	}

	/**
	 * @param string $viewHelperName
	 * @param string $key
	 * @return boolean
	 */
	public function viewHelperVariableContainerGetCallback($viewHelperName, $key) {
		return $this->viewHelperVariableContainerData[$viewHelperName][$key];
	}

	/**
	 * @param AbstractViewHelper $viewHelper
	 * @return void
	 */
	protected function injectDependenciesIntoViewHelper(AbstractViewHelper $viewHelper) {
		$viewHelper->setRenderingContext($this->renderingContext);
		$viewHelper->setArguments($this->arguments);
		if ($viewHelper instanceof AbstractTagBasedViewHelper) {
			$viewHelper->setTagBuilder($this->tagBuilder);
		}
	}

	/**
	 * @return string
	 */
	protected function getViewHelperClassName() {
		$class = get_class($this);
		$class = substr($class, 0, -4);
		$class = str_replace('Tests\\Unit\\', '', $class);
		return $class;
	}
	/**
	 * @param string $type
	 * @param mixed $value
	 * @return NodeInterface
	 */
	protected function createNode($type, $value) {
		if ('Boolean' === $type) {
			$value = $this->createNode('Text', strval($value));
		}
		/** @var NodeInterface $node */
		$className = 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\' . $type . 'Node';
		$node = new $className($value);
		return $node;
	}
	/**
	 * @return ViewHelperInterface
	 */
	protected function createInstance() {
		$className = $this->getViewHelperClassName();
		/** @var ViewHelperInterface $instance */
		$instance = new $className();
		$instance->initialize();
		return $instance;
	}
	/**
	 * @param array $arguments
	 * @param array $variables
	 * @param NodeInterface $childNode
	 * @return ViewHelperInterface
	 */
	protected function buildViewHelperInstance($arguments = array(), $variables = array(), $childNode = NULL) {
		$instance = $this->createInstance();
		$resolver = $this->getMockBuilder('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperResolver')
			->setMethods(array('resolveViewHelperClassName'))
			->getMock();
		$resolver->expects($this->any())->method('resolveViewHelperClassName')->willReturn($this->getViewHelperClassName());
		$this->renderingContext->setVariableProvider(new StandardVariableProvider($variables));
		$this->renderingContext->injectViewHelperVariableContainer(new ViewHelperVariableContainer());
		$this->injectDependenciesIntoViewHelper($instance);
		$arguments = $this->fillMissingArgumentsWithDefaultValues($instance, $arguments);
		$node = new ViewHelperNode($resolver, 'foo', 'bar', $arguments, new ParsingState());
		$instance->setRenderingContext($this->renderingContext);
		$instance->setArguments($arguments);
		$instance->setViewHelperNode($node);
		if (NULL !== $childNode) {
			$node->addChildNode($childNode);
			$instance->setChildNodes(array($childNode));
		}
		return $instance;
	}
	/**
	 * @param array $arguments
	 * @param array $variables
	 * @param NodeInterface $childNode
	 * @return mixed
	 */
	protected function executeViewHelper($arguments = array(), $variables = array(), $childNode = NULL) {
		$instance = $this->buildViewHelperInstance($arguments, $variables, $childNode);
		$output = $instance->initializeArgumentsAndRender();
		return $output;
	}
	/**
	 * @param array $arguments
	 * @param array $variables
	 * @param NodeInterface $childNode
	 * @return mixed
	 */
	protected function executeViewHelperStatic($arguments = array(), $variables = array(), $childNode = NULL) {
		$instance = $this->buildViewHelperInstance($arguments, $variables, $childNode);
		if ($childNode !== NULL) {
			$childClosure = function() use ($childNode) {
				return $childNode->evaluate($this->renderingContext);
			};
		} else {
			$childClosure = function() {};
		}
		$viewHelperClassName = $this->getViewHelperClassName();
		$arguments = $this->fillMissingArgumentsWithDefaultValues($instance, $arguments);
		return $viewHelperClassName::renderStatic($arguments, $childClosure, $this->renderingContext);
	}
	/**
	 * @param string $nodeType
	 * @param mixed $nodeValue
	 * @param array $arguments
	 * @param array $variables
	 * @return mixed
	 */
	protected function executeViewHelperUsingTagContent($nodeType, $nodeValue, $arguments = array(), $variables = array()) {
		$childNode = $this->createNode($nodeType, $nodeValue);
		$instance = $this->buildViewHelperInstance($arguments, $variables, $childNode);
		$output = $instance->initializeArgumentsAndRender();
		return $output;
	}
	/**
	 * @param string $nodeType
	 * @param mixed $nodeValue
	 * @param array $arguments
	 * @param array $variables
	 * @return mixed
	 */
	protected function executeViewHelperUsingTagContentStatic($nodeType, $nodeValue, $arguments = array(), $variables = array()) {
		$childNode = $this->createNode($nodeType, $nodeValue);
		$instance = $this->buildViewHelperInstance($arguments, $variables, $childNode);
		$childClosure = function() use ($childNode) {
			return $childNode->evaluate($this->renderingContext);
		};
		$viewHelperClassName = $this->getViewHelperClassName();
		return $viewHelperClassName::renderStatic($arguments, $childClosure, $this->renderingContext);
	}

	/**
	 * @param ViewHelperInterface $instance
	 * @param array $arguments
	 * @return array
	 */
	protected function fillMissingArgumentsWithDefaultValues(ViewHelperInterface $instance, array $arguments) {
		foreach ($instance->prepareArguments() as $argumentDefinition) {
			$argumentName = $argumentDefinition->getName();
			if (!array_key_exists($argumentName, $arguments)) {
				$arguments[$argumentName] = $argumentDefinition->getDefaultValue();
			}
		}
		return $arguments;
	}

}
