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
 * @package Fluid
 * @subpackage View
 * @version $Id$
 */

/**
 * The main template view. Should be used as view if you want Fluid Templating
 *
 * @package Fluid
 * @subpackage View
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TemplateView extends \F3\FLOW3\MVC\View\AbstractView implements \F3\Fluid\View\TemplateViewInterface {

	/**
	 * Pattern for fetching information from controller object name
	 * @var string
	 */
	const PATTERN_CONTROLLER = '/^F3\\\\\w*\\\\(?:(?P<SubpackageName>.*)\\\\)?Controller\\\\(?P<ControllerName>\w*)Controller$/';

	/**
	 * @var \F3\Fluid\Core\Parser\TemplateParser
	 */
	protected $templateParser;

	/**
	 * File pattern for resolving the template file
	 * @var string
	 */
	protected $templatePathAndFilenamePattern = '@packageResources/Private/Templates/@subpackage@controller/@action.html';

	/**
	 * Directory pattern for global partials. Not part of the public API, should not be changed for now.
	 * @var string
	 * @internal
	 */
	private $globalPartialBasePath = '@packageResources/Private/Templates';

	/**
	 * File pattern for resolving the layout
	 * @var string
	 */
	protected $layoutPathAndFilenamePattern = '@packageResources/Private/Layouts/@layout.html';

	/**
	 * @var array
	 */
	protected $contextVariables = array();

	/**
	 * Path and filename of the template file. If set,  overrides the templatePathAndFilenamePattern
	 * @var string
	 */
	protected $templatePathAndFilename = NULL;

	/**
	 * Path and filename of the layout file. If set, overrides the layoutPathAndFilenamePattern
	 * @var string
	 */
	protected $layoutPathAndFilename = NULL;

	/**
	 * Name of current action to render
	 * @var string
	 */
	protected $actionName;

	/**
	 * Inject the template parser
	 *
	 * @param \F3\Fluid\Core\Parser\TemplateParser $templateParser The template parser
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectTemplateParser(\F3\Fluid\Core\Parser\TemplateParser $templateParser) {
		$this->templateParser = $templateParser;
	}

	/**
	 * Initialize view
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function initializeView() {
		$this->contextVariables['view'] = $this;
	}

	/**
	 * Sets the path and name of of the template file. Effectively overrides the
	 * dynamic resolving of a template file.
	 *
	 * @param string $templatePathAndFilename Template file path
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setTemplatePathAndFilename($templatePathAndFilename) {
		$this->templatePathAndFilename = $templatePathAndFilename;
	}

	/**
	 * Sets the path and name of the layout file. Overrides the dynamic resolving of the layout file.
	 *
	 * @param string $layoutPathAndFilename Path and filename of the layout file
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setLayoutPathAndFilename($layoutPathAndFilename) {
		$this->layoutPathAndFilename = $layoutPathAndFilename;
	}

	/**
	 * Find the XHTML template according to $this->templatePathAndFilenamePattern and render the template.
	 * If "layoutName" is set in a PostParseFacet callback, it will render the file with the given layout.
	 *
	 * @param string $actionName If set, the view of the specified action will be rendered instead. Default is the action specified in the Request object
	 * @return string Rendered Template
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render($actionName = NULL) {
		$this->actionName = $actionName;

		$parsedTemplate = $this->parseTemplate($this->resolveTemplatePathAndFilename());

		$variableContainer = $parsedTemplate->getVariableContainer();
		if ($variableContainer !== NULL && $variableContainer->exists('layoutName')) {
			return $this->renderWithLayout($variableContainer->get('layoutName'));
		}

		$variableContainer = $this->objectFactory->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $this->contextVariables);
		$renderingContext = $this->objectFactory->create('F3\Fluid\Core\RenderingContext');
		$renderingContext->setTemplateVariableContainer($variableContainer);
		$renderingContext->setControllerContext($this->controllerContext);

		return $parsedTemplate->render($renderingContext);
	}

	/**
	 * Renders a given section.
	 *
	 * @param string $sectionName Name of section to render
	 * @return rendered template for the section
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function renderSection($sectionName) {
		$parsedTemplate = $this->parseTemplate($this->resolveTemplatePathAndFilename());

		$templateTree = $parsedTemplate->getRootNode();

		$sections = $parsedTemplate->getVariableContainer()->get('sections');
		if(!array_key_exists($sectionName, $sections)) {
			throw new \F3\Fluid\Core\RuntimeException('The given section does not exist!', 1227108982);
		}
		$section = $sections[$sectionName];

		$variableContainer = $this->objectFactory->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $this->contextVariables);
		$renderingContext = $this->objectFactory->create('F3\Fluid\Core\RenderingContext');
		$renderingContext->setTemplateVariableContainer($variableContainer);
		$renderingContext->setControllerContext($this->controllerContext);

		$section->setRenderingContext($renderingContext);
		return $section->evaluate();
	}

	/**
	 * Render a template with a given layout.
	 *
	 * @param string $layoutName Name of layout
	 * @return string rendered HTML
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function renderWithLayout($layoutName) {
		$parsedTemplate = $this->parseTemplate($this->resolveLayoutPathAndFilename($layoutName));

		$variableContainer = $this->objectFactory->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $this->contextVariables);
		$renderingContext = $this->objectFactory->create('F3\Fluid\Core\RenderingContext');
		$renderingContext->setTemplateVariableContainer($variableContainer);
		$renderingContext->setControllerContext($this->controllerContext);

		return $parsedTemplate->render($renderingContext);
	}

	/**
	 * Renders a partial. If $partialName starts with /, the partial is resolved globally. Else, locally.
	 * SHOULD NOT BE USED BY USERS!
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function renderPartial($partialName, $sectionToRender, array $variables) {
		if ($partialName[0] === '/') {
			$partialBasePath = str_replace('@package', $this->packageManager->getPackagePath($this->controllerContext->getRequest()->getControllerPackageKey()), $this->globalPartialBasePath);
			$partialName = substr($partialName, 1);
		} else {
			$partialBasePath = dirname($this->resolveTemplatePathAndFilename());
		}
		$partialNameSplitted = explode('/', $partialName);
		$partialFileName = '_' . array_pop($partialNameSplitted) . '.html';
		$partialDirectoryName = $partialBasePath . '/' . implode('/', $partialNameSplitted);

		$partialPathAndFileName = $partialDirectoryName . '/' . $partialFileName;

		$partial = $this->parseTemplate($partialPathAndFileName);
		$variables['view'] = $this;
		$variableContainer = $this->objectFactory->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $variables);

		$renderingContext = $this->objectFactory->create('F3\Fluid\Core\RenderingContext');
		$renderingContext->setTemplateVariableContainer($variableContainer);
		$renderingContext->setControllerContext($this->controllerContext);

		return $parsedTemplate->render($renderingContext);
		if ($sectionToRender !== NULL) {
			$sections = $partial->getVariableContainer()->get('sections');
			if(!array_key_exists($sectionToRender, $sections)) {
				throw new \F3\Fluid\Core\RuntimeException('The given section does not exist!', 1227108983);
			}
			$syntaxTree = $sections[$sectionToRender];
		} else {
			$syntaxTree = $partial->getRootNode();
		}
		$syntaxTree->setRenderingContext($renderingContext);
		return $syntaxTree->evaluate();
	}

	/**
	 * Add a variable to the context.
	 * Can be chained, so $template->addVariable(..., ...)->addVariable(..., ...); is possible,
	 *
	 * @param string $key Key of variable
	 * @param object $value Value of object
	 * @return \F3\Fluid\View\TemplateView an instance of $this, to enable chaining.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function assign($key, $value) {
		if ($key === 'view') {
			throw new \F3\Fluid\Core\RuntimeException('The variable "view" cannot be set using assign().', 1233317880);
		}
		$this->contextVariables[$key] = $value;
		return $this;
	}

	/**
	 * Return the current request
	 *
	 * @return \F3\FLOW3\MVC\Web\Request the current request
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function getRequest() {
		return $this->controllerContext->getRequest();
	}

	/**
	 * Checks whether a template can be resolved for the current request context.
	 *
	 * @return boolean
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function hasTemplate() {
		return file_exists($this->resolveTemplatePathAndFilename());
	}

	/**
	 * Parse the given template and return it.
	 *
	 * Will cache the results for one call.
	 *
	 * @param $templatePathAndFilename absolute filename of the template to be parsed
	 * @return \F3\Fluid\Core\Parser\ParsedTemplateInterface the parsed template tree
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function parseTemplate($templatePathAndFilename) {
		$templateSource = \F3\FLOW3\Utility\Files::getFileContents($templatePathAndFilename, FILE_TEXT);
		if ($templateSource === FALSE) {
			throw new \F3\Fluid\Core\RuntimeException('The template file "' . $templatePathAndFilename . '" could not be loaded.', 1225709595);
		}
		return $this->templateParser->parse($templateSource);
	}

	/**
	 * Resolve the path and name of the template, based on $this->templatePathAndFilename and $this->templatePathAndFilenamePattern.
	 * In case a template has been set with $this->setTemplatePathAndFilename, it just uses the given template file.
	 * Otherwise, it resolves the $this->templatePathAndFilenamePattern
	 *
	 * @return string Path and filename of template file
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function resolveTemplatePathAndFilename() {
		if ($this->templatePathAndFilename !== NULL) {
			return $this->templatePathAndFilename;
		} else {
			$actionName = ($this->actionName !== NULL ? $this->actionName : $this->controllerContext->getRequest()->getControllerActionName());
			$matches = array();
			preg_match(self::PATTERN_CONTROLLER, $this->controllerContext->getRequest()->getControllerObjectName(), $matches);
			$subpackageName = '';
			if ($matches['SubpackageName'] !== '') {
				$subpackageName = str_replace('\\', '/', $matches['SubpackageName']);
				$subpackageName .= '/';
			}
			$controllerName = $matches['ControllerName'];
			$templatePathAndFilename = str_replace('@package', $this->packageManager->getPackagePath($this->controllerContext->getRequest()->getControllerPackageKey()), $this->templatePathAndFilenamePattern);
			$templatePathAndFilename = str_replace('@subpackage', $subpackageName, $templatePathAndFilename);
			$templatePathAndFilename = str_replace('@controller', $controllerName, $templatePathAndFilename);
			$templatePathAndFilename = str_replace('@action', strtolower($actionName), $templatePathAndFilename);

			return $templatePathAndFilename;
		}
	}

	/**
	 * Resolve the path and file name of the layout fil, based on $this->layoutPathAndFilename and
	 * $this->layoutPathAndFilenamePattern.
	 *
	 * In case a layout has already been set with setLayoutPathAndFilename(), this method returns that
	 * path, otherwise a path and filename will be resolved using the layoutPathAndFilenamePattern.
	 *
	 * @param string $layoutName Name of the layout to use. If none used, use "default"
	 * @return string Path and filename of layout file
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function resolveLayoutPathAndFilename($layoutName = 'default') {
		if ($this->layoutPathAndFilename) {
			return $this->layoutPathAndFilename;
		} else {
			$layoutPathAndFilename = str_replace('@package', $this->packageManager->getPackagePath($this->controllerContext->getRequest()->getControllerPackageKey()), $this->layoutPathAndFilenamePattern);
			$layoutPathAndFilename = str_replace('@layout', $layoutName, $layoutPathAndFilename);
			return $layoutPathAndFilename;
		}
	}
}
?>