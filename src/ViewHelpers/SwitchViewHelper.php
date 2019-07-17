<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
class SwitchViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var mixed
     */
    protected $backupSwitchExpression = null;

    /**
     * @var boolean
     */
    protected $backupBreakState = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('expression', 'mixed', 'Expression to switch', true);
    }

    /**
     * @return mixed the rendered string
     * @api
     */
    public function render()
    {
        $expression = $this->arguments['expression'];
        $this->backupSwitchState();
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();

        $variableContainer->addOrUpdate(SwitchViewHelper::class, 'switchExpression', $expression);
        $variableContainer->addOrUpdate(SwitchViewHelper::class, 'break', false);

        $content = $this->retrieveContentFromChildNodes($this->getChildNodes());

        if ($variableContainer->exists(SwitchViewHelper::class, 'switchExpression')) {
            $variableContainer->remove(SwitchViewHelper::class, 'switchExpression');
        }
        if ($variableContainer->exists(SwitchViewHelper::class, 'break')) {
            $variableContainer->remove(SwitchViewHelper::class, 'break');
        }

        $this->restoreSwitchState();
        return $content;
    }

    /**
     * @param NodeInterface[] $childNodes
     * @return mixed
     */
    protected function retrieveContentFromChildNodes(array $childNodes)
    {
        $content = null;
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();

        foreach ($childNodes as $childNode) {

            if (!$childNode instanceof CaseViewHelper && !$childNode instanceof DefaultCaseViewHelper) {
                continue;
            }

            if ($variableContainer->get(SwitchViewHelper::class, 'break') === true) {
                break;
            }

            $content = $childNode->evaluate($this->renderingContext);
        }

        return $content;
    }

    /**
     * Backups "switch expression" and "break" state of a possible parent switch ViewHelper to support nesting
     *
     * @return void
     */
    protected function backupSwitchState(): void
    {
        if ($this->renderingContext->getViewHelperVariableContainer()->exists(SwitchViewHelper::class, 'switchExpression')) {
            $this->backupSwitchExpression = $this->renderingContext->getViewHelperVariableContainer()->get(SwitchViewHelper::class, 'switchExpression');
        }
        if ($this->renderingContext->getViewHelperVariableContainer()->exists(SwitchViewHelper::class, 'break')) {
            $this->backupBreakState = $this->renderingContext->getViewHelperVariableContainer()->get(SwitchViewHelper::class, 'break');
        }
    }

    /**
     * Restores "switch expression" and "break" states that might have been backed up in backupSwitchState() before
     *
     * @return void
     */
    protected function restoreSwitchState(): void
    {
        if ($this->backupSwitchExpression !== null) {
            $this->renderingContext->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'switchExpression', $this->backupSwitchExpression);
        }
        if ($this->backupBreakState !== null) {
            $this->renderingContext->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'break', $this->backupBreakState);
        }
    }
}
