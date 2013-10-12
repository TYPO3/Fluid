<?php
namespace TYPO3\Fluid\Core\Parser;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\View;

/**
 * Stores all information relevant for one parsing pass - that is, the root node,
 * and the current stack of open nodes (nodeStack) and a variable container used
 * for PostParseFacets.
 */
class ParsingState implements ParsedTemplateInterface {

	/**
	 * Root node reference
	 *
	 * @var RootNode
	 */
	protected $rootNode;

	/**
	 * Array of node references currently open.
	 *
	 * @var array
	 */
	protected $nodeStack = array();

	/**
	 * Variable container where ViewHelpers implementing the PostParseFacet can
	 * store things in.
	 *
	 * @var TemplateVariableContainer
	 */
	protected $variableContainer;

	/**
	 * The layout name of the current template or NULL if the template does not contain a layout definition
	 *
	 * @var AbstractNode
	 */
	protected $layoutNameNode;

	/**
	 * @var boolean
	 */
	protected $compilable = TRUE;

	/**
	 * Injects a variable container. ViewHelpers implementing the PostParse
	 * Facet can store information inside this variableContainer.
	 *
	 * @param TemplateVariableContainer $variableContainer
	 * @return void
	 */
	public function injectVariableContainer(TemplateVariableContainer $variableContainer) {
		$this->variableContainer = $variableContainer;
	}

	/**
	 * Set root node of this parsing state
	 *
	 * @param AbstractNode $rootNode
	 * @return void
	 */
	public function setRootNode(AbstractNode $rootNode) {
		$this->rootNode = $rootNode;
	}

	/**
	 * Get root node of this parsing state.
	 *
	 * @return AbstractNode The root node
	 */
	public function getRootNode() {
		return $this->rootNode;
	}

	/**
	 * Render the parsed template with rendering context
	 *
	 * @param RenderingContextInterface $renderingContext The rendering context to use
	 * @return string Rendered string
	 */
	public function render(RenderingContextInterface $renderingContext) {
		return $this->rootNode->evaluate($renderingContext);
	}

	/**
	 * Push a node to the node stack. The node stack holds all currently open
	 * templating tags.
	 *
	 * @param AbstractNode $node Node to push to node stack
	 * @return void
	 */
	public function pushNodeToStack(AbstractNode $node) {
		array_push($this->nodeStack, $node);
	}

	/**
	 * Get the top stack element, without removing it.
	 *
	 * @return AbstractNode the top stack element.
	 */
	public function getNodeFromStack() {
		return $this->nodeStack[count($this->nodeStack) - 1];
	}

	/**
	 * Pop the top stack element (=remove it) and return it back.
	 *
	 * @return AbstractNode the top stack element, which was removed.
	 */
	public function popNodeFromStack() {
		return array_pop($this->nodeStack);
	}

	/**
	 * Count the size of the node stack
	 *
	 * @return integer Number of elements on the node stack (i.e. number of currently open Fluid tags)
	 */
	public function countNodeStack() {
		return count($this->nodeStack);
	}

	/**
	 * Returns a variable container which will be then passed to the postParseFacet.
	 *
	 * @return TemplateVariableContainer The variable container or NULL if none has been set yet
	 * @todo Rename to getPostParseVariableContainer
	 */
	public function getVariableContainer() {
		return $this->variableContainer;
	}

	/**
	 * @param AbstractNode $layoutNameNode name of the layout that is defined in this template via <f:layout name="..." />
	 * @return void
	 */
	public function setLayoutNameNode(AbstractNode $layoutNameNode) {
		$this->layoutNameNode = $layoutNameNode;
	}

	/**
	 * @return AbstractNode
	 */
	public function getLayoutNameNode() {
		return $this->layoutNameNode;
	}

	/**
	 * Returns TRUE if the current template has a template defined via <f:layout name="..." />
	 * @see getLayoutName()
	 *
	 * @return boolean
	 */
	public function hasLayout() {
		return $this->layoutNameNode !== NULL;
	}

	/**
	 * Returns the name of the layout that is defined within the current template via <f:layout name="..." />
	 * If no layout is defined, this returns NULL
	 * This requires the current rendering context in order to be able to evaluate the layout name
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 * @throws View\Exception
	 */
	public function getLayoutName(RenderingContextInterface $renderingContext) {
		if (!$this->hasLayout()) {
			return NULL;
		}
		$layoutName = $this->layoutNameNode->evaluate($renderingContext);
		if (!empty($layoutName)) {
			return $layoutName;
		}
		throw new View\Exception('The layoutName could not be evaluated to a string', 1296805368);
	}

	/**
	 * @return boolean
	 */
	public function isCompilable() {
		return $this->compilable;
	}

	/**
	 * @param boolean $compilable
	 */
	public function setCompilable($compilable) {
		$this->compilable = $compilable;
	}

	/**
	 * @return boolean
	 */
	public function isCompiled() {
		return FALSE;
	}
}
