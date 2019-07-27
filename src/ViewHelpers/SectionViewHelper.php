<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\EmbeddedComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A ViewHelper to declare sections in templates for later use with e.g. the RenderViewHelper.
 *
 * = Examples =
 *
 * <code title="Rendering sections">
 * <f:section name="someSection">This is a section. {foo}</f:section>
 * <f:render section="someSection" arguments="{foo: someVariable}" />
 * </code>
 * <output>
 * the content of the section "someSection". The content of the variable {someVariable} will be available in the partial as {foo}
 * </output>
 *
 * <code title="Rendering recursive sections">
 * <f:section name="mySection">
 *  <ul>
 *    <f:for each="{myMenu}" as="menuItem">
 *      <li>
 *        {menuItem.text}
 *        <f:if condition="{menuItem.subItems}">
 *          <f:render section="mySection" arguments="{myMenu: menuItem.subItems}" />
 *        </f:if>
 *      </li>
 *    </f:for>
 *  </ul>
 * </f:section>
 * <f:render section="mySection" arguments="{myMenu: menu}" />
 * </code>
 * <output>
 * <ul>
 *   <li>menu1
 *     <ul>
 *       <li>menu1a</li>
 *       <li>menu1b</li>
 *     </ul>
 *   </li>
 * [...]
 * (depending on the value of {menu})
 * </output>
 *
 * @api
 */
class SectionViewHelper extends AbstractViewHelper implements EmbeddedComponentInterface
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the section', true);
    }

    public function onOpen(RenderingContextInterface $renderingContext): ComponentInterface
    {
        parent::onOpen($renderingContext);
        $this->name = $this->getArguments()['name'];
        return $this;
    }
}
