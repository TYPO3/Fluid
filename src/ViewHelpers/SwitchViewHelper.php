<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
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

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $this->getArguments()->setRenderingContext($renderingContext);
        $content = null;

        foreach ($this->getChildren() as $childNode) {
            if ($childNode instanceof DefaultCaseViewHelper || ($childNode instanceof CaseViewHelper && $childNode->getArguments()->setRenderingContext($renderingContext)['value'] == $this->arguments['expression'])) {
                $content = $childNode->evaluate($renderingContext);
                break;
            }
        }
        return $content;
    }
}
