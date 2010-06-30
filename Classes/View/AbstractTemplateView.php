<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\View;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Abstract Fluid Template View.
 *
 * Contains the fundamental methods which any Fluid based template view needs.
 *
 * @version $Id: TemplateView.php 4607 2010-06-22 06:02:55Z sebastian $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractTemplateView implements \F3\Fluid\View\TemplateViewInterface {

	/**
	 * Constants defining possible rendering types
	 */
	const RENDERING_TEMPLATE = 1;
	const RENDERING_PARTIAL = 2;
	const RENDERING_LAYOUT = 3;

	/**
	 * @var \F3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \F3\Fluid\Core\Parser\TemplateParser
	 */
	protected $templateParser;

	/**
	 * The initial rendering context for this template view.
	 * Due to the rendering stack, another rendering context might be active
	 * at certain points while rendering the template.
	 *
	 * @var \F3\Fluid\Core\Rendering\RenderingContextInterface
	 */
	protected $baseRenderingContext;

	/**
	 * Stack containing the current rendering type, the current rendering context, and the current parsed template
	 * Do not manipulate directly, instead use the methods"getCurrent*()", "startRendering(...)" and "stopRendering()"
	 * @var array
	 */
	protected $renderingStack = array();

	/**
	 * Injects the Object Manager
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Inject the Template Parser
	 *
	 * @param \F3\Fluid\Core\Parser\TemplateParser $templateParser The template parser
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectTemplateParser(\F3\Fluid\Core\Parser\TemplateParser $templateParser) {
		$this->templateParser = $templateParser;
	}

	/**
	 * Injects a fresh rendering context
	 *
	 * @param \F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setRenderingContext(\F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		$this->baseRenderingContext = $renderingContext;
		$this->baseRenderingContext->getViewHelperVariableContainer()->setView($this);
		$this->controllerContext = $renderingContext->getControllerContext();
	}

	/**
	 * Sets the current controller context
	 *
	 * @param \F3\FLOW3\MVC\Controller\ControllerContext $controllerContext
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function setControllerContext(\F3\FLOW3\MVC\Controller\ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	/**
	 * Assign a value to the variable container.
	 *
	 * @param string $key The key of a view variable to set
	 * @param mixed $value The value of the view variable
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function assign($key, $value) {
		$templateVariableContainer = $this->baseRenderingContext->getTemplateVariableContainer();
		if ($templateVariableContainer->exists($key)) {
			$templateVariableContainer->remove($key);
		}
		$templateVariableContainer->add($key, $value);
	}

	/**
	 * Assigns multiple values to the JSON output.
	 * However, only the key "value" is accepted.
	 *
	 * @param array $values Keys and values - only a value with key "value" is considered
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function assignMultiple(array $values) {
		$templateVariableContainer = $this->baseRenderingContext->getTemplateVariableContainer();
		foreach ($values as $key => $value) {
			if ($templateVariableContainer->exists($key)) {
				$templateVariableContainer->remove($key);
			}
			$templateVariableContainer->add($key, $value);
		}
	}

	/**
	 * Loads the template source and render the template.
	 * If "layoutName" is set in a PostParseFacet callback, it will render the file with the given layout.
	 *
	 * @param string $actionName If set, the view of the specified action will be rendered instead. Default is the action specified in the Request object
	 * @return string Rendered Template
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function render($actionName = NULL) {
		$this->baseRenderingContext->setControllerContext($this->controllerContext);
		$this->templateParser->setConfiguration($this->buildParserConfiguration());
		$parsedTemplate = $this->templateParser->parse($this->getTemplateSource($actionName));

		if ($this->isLayoutDefinedInTemplate($parsedTemplate)) {
			$this->startRendering(self::RENDERING_LAYOUT, $parsedTemplate, $this->baseRenderingContext);
			$parsedLayout = $this->templateParser->parse($this->getLayoutSource($this->getLayoutNameInTemplate($parsedTemplate)));
			$output = $parsedLayout->render($this->baseRenderingContext);
			$this->stopRendering();
		} else {
			$this->startRendering(self::RENDERING_TEMPLATE, $parsedTemplate, $this->baseRenderingContext);
			$output = $parsedTemplate->render($this->baseRenderingContext);
			$this->stopRendering();
		}

		return $output;
	}

	/**
	 * Renders a given section.
	 *
	 * @param string $sectionName Name of section to render
	 * @param array the variables to use.
	 * @return string rendered template for the section
	 * @throws \F3\Fluid\View\Exception\InvalidSectionException
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderSection($sectionName, array $variables) {
		$parsedTemplate = $this->getCurrentParsedTemplate();

		$sections = $parsedTemplate->getVariableContainer()->get('sections');
		if(!array_key_exists($sectionName, $sections)) {
			throw new \F3\Fluid\View\Exception\InvalidSectionException('The given section does not exist!', 1227108982);
		}
		$section = $sections[$sectionName];

		$renderingContext = $this->getCurrentRenderingContext();
		if ($this->getCurrentRenderingType() === self::RENDERING_LAYOUT) {
			// in case we render a layout right now, we will render a section inside a TEMPLATE.
			$renderingTypeOnNextLevel = self::RENDERING_TEMPLATE;
		} else {
			$variableContainer = $this->objectManager->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $variables);
			$renderingContext = clone $renderingContext;
			$renderingContext->injectTemplateVariableContainer($variableContainer);
			$renderingTypeOnNextLevel = $this->getCurrentRenderingType();
		}

		$renderingContext->getViewHelperVariableContainer()->add('F3\Fluid\ViewHelpers\SectionViewHelper', 'isCurrentlyRenderingSection', 'TRUE');

		$this->startRendering($renderingTypeOnNextLevel, $parsedTemplate, $renderingContext);
		$output = $section->evaluate($renderingContext);
		$this->stopRendering();

		return $output;
	}

	/**
	 * Renders a partial.
	 *
	 * @param string $partialName
	 * @param string $sectionName
	 * @param array $variables
	 * @param \F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer $viewHelperVariableContainer the View Helper Variable container to use.
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function renderPartial($partialName, $sectionName, array $variables) {
		$partial = $this->templateParser->parse($this->getPartialSource($partialName));
		$variableContainer = $this->objectManager->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $variables);
		$renderingContext = clone $this->getCurrentRenderingContext();
		$renderingContext->injectTemplateVariableContainer($variableContainer);

		$this->startRendering(self::RENDERING_PARTIAL, $partial, $renderingContext);
		if ($sectionName !== NULL) {
			$output = $this->renderSection($sectionName, $variables);
		} else {
			$output = $partial->render($renderingContext);
		}
		$this->stopRendering();

		return $output;
	}

	/**
	 * Resolve the template path and filename for the given action. If $actionName
	 * is NULL, looks into the current request.
	 *
	 * @param string $actionName Name of the action. If NULL, will be taken from request.
	 * @return string Full path to template
	 * @throws \F3\Fluid\View\Exception\InvalidTemplateResourceException in case the template was not found
	 */
	abstract protected function getTemplateSource($actionName);

	/**
	 * Resolve the path and file name of the layout file, based on
	 * $this->layoutPathAndFilename and $this->layoutPathAndFilenamePattern.
	 *
	 * In case a layout has already been set with setLayoutPathAndFilename(),
	 * this method returns that path, otherwise a path and filename will be
	 * resolved using the layoutPathAndFilenamePattern.
	 *
	 * @param string $layoutName Name of the layout to use. If none given, use "default"
	 * @return string Path and filename of layout file
	 * @throws \F3\Fluid\View\Exception\InvalidTemplateResourceException
	 */
	abstract protected function getLayoutSource($layoutName = 'default');

	/**
	 * Figures out which partial to use.
	 *
	 * @param string $partialName The name of the partial
	 * @return string the full path which should be used. The path definitely exists.
	 * @throws \F3\Fluid\View\Exception\InvalidTemplateResourceException
	 */
	abstract protected function getPartialSource($partialName);

	/**
	 * Build parser configuration
	 *
	 * @return \F3\Fluid\Core\Parser\Configuration
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function buildParserConfiguration() {
		$parserConfiguration = $this->objectManager->create('F3\Fluid\Core\Parser\Configuration');
		if ($this->controllerContext->getRequest()->getFormat() === 'html') {
			$parserConfiguration->addInterceptor($this->objectManager->get('F3\Fluid\Core\Parser\Interceptor\Escape'));
			$parserConfiguration->addInterceptor($this->objectManager->get('F3\Fluid\Core\Parser\Interceptor\Resource'));
		}
		return $parserConfiguration;
	}

	/**
	 * Returns TRUE if there is a layout defined in the given template via a <f:layout name="..." /> tag.
	 *
	 * @param \F3\Fluid\Core\Parser\ParsedTemplateInterface $parsedTemplate
	 * @return boolean TRUE if a layout has been defined, FALSE otherwise.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function isLayoutDefinedInTemplate(\F3\Fluid\Core\Parser\ParsedTemplateInterface $parsedTemplate) {
		$variableContainer = $parsedTemplate->getVariableContainer();
		return ($variableContainer !== NULL && $variableContainer->exists('layoutName'));
	}

	/**
	 * Returns the name of the layout defined in the template, if one exists.
	 *
	 * @param \F3\Fluid\Core\Parser\ParsedTemplateInterface $parsedTemplate
	 * @return string the Layout name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getLayoutNameInTemplate(\F3\Fluid\Core\Parser\ParsedTemplateInterface $parsedTemplate) {
		if ($this->isLayoutDefinedInTemplate($parsedTemplate)) {
			return $parsedTemplate->getVariableContainer()->get('layoutName');
		}
		return NULL;
	}

	/**
	 * Start a new nested rendering. Pushes the given information onto the $renderingStack.
	 *
	 * @param int $type one of the RENDERING_* constants
	 * @param \F3\Fluid\Core\Parser\ParsedTemplateInterface $parsedTemplate
	 * @param \F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function startRendering($type, \F3\Fluid\Core\Parser\ParsedTemplateInterface $parsedTemplate, \F3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		array_push($this->renderingStack, array('type' => $type, 'parsedTemplate' => $parsedTemplate, 'renderingContext' => $renderingContext));
	}

	/**
	 * Stops the current rendering. Removes one element from the $renderingStack. Make sure to always call this
	 * method pair-wise with startRendering().
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function stopRendering() {
		array_pop($this->renderingStack);
	}

	/**
	 * Get the current rendering type.
	 *
	 * @return one of RENDERING_* constants
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getCurrentRenderingType() {
		$currentRendering = end($this->renderingStack);
		return $currentRendering['type'];
	}

	/**
	 * Get the parsed template which is currently being rendered.
	 *
	 * @return F3\Fluid\Core\Parser\ParsedTemplateInterface
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getCurrentParsedTemplate() {
		$currentRendering = end($this->renderingStack);
		return $currentRendering['parsedTemplate'];
	}

	/**
	 * Get the rendering context which is currently used.
	 *
	 * @return F3\Fluid\Core\Rendering\RenderingContextInterface
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getCurrentRenderingContext() {
		$currentRendering = end($this->renderingStack);
		return $currentRendering['renderingContext'];
	}
}

?>