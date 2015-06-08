<?php
namespace NamelessCoder\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Switch view helper which can be used to render content depending on a value or expression.
 * Implements what a basic switch()-PHP-method does.
 *
 * An optional default case can be specified which is rendered if none of the "f:case" conditions matches.
 *
 * = Examples =
 *
 * <code title="Simple Switch statement">
 * <f:switch expression="{person.gender}">
 *   <f:case value="male">Mr.</f:case>
 *   <f:case value="female">Mrs.</f:case>
 *   <f:defaultCase>Mr. / Mrs.</f:defaultCase>
 * </f:switch>
 * </code>
 * <output>
 * "Mr.", "Mrs." or "Mr. / Mrs." (depending on the value of {person.gender})
 * </output>
 *
 * Note: Using this view helper can be a sign of weak architecture. If you end up using it extensively
 * you might want to consider restructuring your controllers/actions and/or use partials and sections.
 * E.g. the above example could be achieved with <f:render partial="title.{person.gender}" /> and the partials
 * "title.male.html", "title.female.html", ...
 * Depending on the scenario this can be easier to extend and possibly contains less duplication.
 *
 * @api
 */
class SwitchViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @var mixed
	 */
	protected $backupSwitchExpression = NULL;

	/**
	 * @var boolean
	 */
	protected $backupBreakState = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('expression', 'mixed', 'Expression to switch', TRUE);
	}

	/**
	 * @return string the rendered string
	 * @api
	 */
	public function render() {
		$expression = $this->arguments['expression'];
		$content = '';
		$this->backupSwitchState();
		$variableContainer = $this->renderingContext->getViewHelperVariableContainer();

		$variableContainer->addOrUpdate('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression', $expression);
		$variableContainer->addOrUpdate('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break', FALSE);

		$content = $this->retrieveContentFromChildNodes($this->childNodes);

		if ($variableContainer->exists('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression')) {
			$variableContainer->remove('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression');
		}
		if ($variableContainer->exists('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break')) {
			$variableContainer->remove('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break');
		}

		$this->restoreSwitchState();
		return $content;
	}

	/**
	 * @param NodeInterface[] $childNodes
	 * @return mixed
	 */
	protected function retrieveContentFromChildNodes(array $childNodes) {
		$content = NULL;
		$defaultCaseViewHelperNode = NULL;
		foreach ($this->childNodes as $childNode) {
			if ($this->isDefaultCaseNode($childNode)) {
				$defaultCaseViewHelperNode = $childNode;
			}
			if (!$this->isCaseNode($childNode)) {
				continue;
			}
			$content = $childNode->evaluate($this->renderingContext);
			if ($this->viewHelperVariableContainer->get('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break') === TRUE) {
				$defaultCaseViewHelperNode = NULL;
				break;
			}
		}

		if ($defaultCaseViewHelperNode !== NULL) {
			$content = $defaultCaseViewHelperNode->evaluate($this->renderingContext);
		}
		return $content;
	}

	/**
	 * @param NodeInterface $node
	 * @return boolean
	 */
	protected function isDefaultCaseNode(NodeInterface $node) {
		return ($node instanceof ViewHelperNode && $node->getViewHelperClassName() === 'NamelessCoder\Fluid\ViewHelpers\DefaultCaseViewHelper');
	}

	/**
	 * @param NodeInterface $node
	 * @return boolean
	 */
	protected function isCaseNode(NodeInterface $node) {
		return ($node instanceof ViewHelperNode && $node->getViewHelperClassName() === 'NamelessCoder\Fluid\ViewHelpers\CaseViewHelper');
	}

	/**
	 * Backups "switch expression" and "break" state of a possible parent switch ViewHelper to support nesting
	 *
	 * @return void
	 */
	protected function backupSwitchState() {
		if ($this->renderingContext->getViewHelperVariableContainer()->exists('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression')) {
			$this->backupSwitchExpression = $this->renderingContext->getViewHelperVariableContainer()->get('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression');
		}
		if ($this->renderingContext->getViewHelperVariableContainer()->exists('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break')) {
			$this->backupBreakState = $this->renderingContext->getViewHelperVariableContainer()->get('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break');
		}
	}

	/**
	 * Restores "switch expression" and "break" states that might have been backed up in backupSwitchState() before
	 *
	 * @return void
	 */
	protected function restoreSwitchState() {
		if ($this->backupSwitchExpression !== NULL) {
			$this->renderingContext->getViewHelperVariableContainer()->addOrUpdate('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression', $this->backupSwitchExpression);
		}
		if ($this->backupBreakState !== FALSE) {
			$this->renderingContext->getViewHelperVariableContainer()->addOrUpdate('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break', TRUE);
		}
	}
}
