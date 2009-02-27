<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\View;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage View
 * @version $Id:$
 */
/**
 * The main template view. Should be used as view if you want Fluid Templating
 *
 * @package Fluid
 * @subpackage View
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TemplateView extends \F3\FLOW3\MVC\View\AbstractView {

	/**
	 * @var \F3\Fluid\Core\TemplateParser
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
	 * Syntax tree cache. The key will be the file name (including path), the value the generated syntax tree.
	 * @var array
	 */
	protected $localSyntaxTreeCache = array();

	/**
	 * Syntax tree cache (persistent)
	 * @var \F3\FLOW3\Cache\Frontend\VariableFrontend
	 */
	protected $syntaxTreeCache;

	/**
	 * Sets the cache
	 *
	 *
	 * @param \F3\FLOW3\Cache\Frontend\VariableFrontend $cache Cache for the reflection service
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectSyntaxTreeCache(\F3\FLOW3\Cache\Frontend\VariableFrontend $syntaxTreeCache) {
		$this->syntaxTreeCache = $syntaxTreeCache;
	}

	/**
	 * Inject the template parser
	 *
	 * @param \F3\Fluid\Core\TemplateParser $templateParser The template parser
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectTemplateParser(\F3\Fluid\Core\TemplateParser $templateParser) {
		$this->templateParser = $templateParser;
	}

	/**
	 * Initialize view
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeView() {
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
		$templateTree = $parsedTemplate->getRootNode();
		return $templateTree->render($this->objectFactory->create('F3\Fluid\Core\VariableContainer', $this->contextVariables));
	}

	/**
	 * Renders a given section.
	 *
	 * @param string $sectionName Name of section to render
	 * @return rendered template for the section
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderSection($sectionName) {
		$parsedTemplate = $this->parseTemplate($this->resolveTemplatePathAndFilename());

		$templateTree = $parsedTemplate->getRootNode();

		$sections = $parsedTemplate->getVariableContainer()->get('sections');
		if(!array_key_exists($sectionName, $sections)) throw new \F3\Fluid\Core\RuntimeException('The given section does not exist!', 1227108982);

		return $sections[$sectionName]->render($this->objectFactory->create('F3\Fluid\Core\VariableContainer', $this->contextVariables));
	}

	/**
	 * Render a template with a given layout.
	 *
	 * @param string $layoutName Name of layout
	 * @return string rendered HTML
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderWithLayout($layoutName) {
		$layout = $this->parseTemplate($this->resolveLayoutPathAndFilename($layoutName));
		$layoutTree = $layout->getRootNode();

		$variableStore = $this->objectFactory->create('F3\Fluid\Core\VariableContainer', $this->contextVariables);
		$result = $layoutTree->render($variableStore);

		return $result;
	}

	/**
	 * Renders a partial. If $partialName starts with /, the partial is resolved globally. Else, locally.
	 * SHOULD NOT BE USED BY USERS!
	 * @internal
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderPartial($partialName, array $variables) {
		if ($partialName{0} === '/') {
			$partialBasePath = str_replace('@package', $this->packageManager->getPackagePath($this->request->getControllerPackageKey()), $this->globalPartialBasePath);
			$partialName = substr($partialName, 1);
		} else {
			$partialBasePath = dirname($this->resolveTemplatePathAndFilename());
		}
		$partialNameSplitted = explode('/', $partialName);
		$partialFileName = '_' . array_pop($partialNameSplitted) . '.html';
		$partialDirectoryName = $partialBasePath . '/' . implode('/', $partialNameSplitted);

		$partialPathAndFileName = $partialDirectoryName . '/' . $partialFileName;

		$partial = $this->parseTemplate($partialPathAndFileName);
		$syntaxTree = $partial->getRootNode();

		$variables['view'] = $this;
		$variableStore = $this->objectFactory->create('F3\Fluid\Core\VariableContainer', $variables);
		$result = $syntaxTree->render($variableStore);

		return $result;
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
		if ($key === 'view') throw new \F3\Fluid\Core\RuntimeException('The variable "view" cannot be set using assign().', 1233317880);
		$this->contextVariables[$key] = $value;
		return $this;
	}

	/**
	 * Add a variable to the context. DEPRECATED; use "assign" instead.
	 *
	 * This is an alias to the "assign" method.
	 * @todo remove this alias after changing all production code
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @deprecated
	 */
	public function addVariable($key, $value) {
		trigger_error('Call to deprecated method addVariable() in Fluid\View\TemplateView', E_USER_DEPRECATED);
		return $this->assign($key, $value);
	}

	/**
	 * Return the current request
	 *
	 * @return \F3\FLOW3\MVC\Web\Request the current request
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Parse the given template and return it.
	 *
	 * Will cache the results for one call.
	 * @return \F3\Fluid\Core\ParsedTemplateInterface
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function parseTemplate($templatePathAndFilename) {
		if (array_key_exists($templatePathAndFilename, $this->localSyntaxTreeCache)) {
			return $this->localSyntaxTreeCache[$templatePathAndFilename];
		}

		$parsedTemplate = $this->syntaxTreeCache->get(md5($templatePathAndFilename));
		if (!$parsedTemplate) {
			$templateSource = \F3\FLOW3\Utility\Files::getFileContents($templatePathAndFilename, FILE_TEXT);
			if ($templateSource === FALSE) throw new \F3\Fluid\Core\RuntimeException('The template file "' . $templatePathAndFilename . '" could not be loaded.', 1225709595);
			$parsedTemplate = $this->templateParser->parse($templateSource);

			$this->syntaxTreeCache->set(md5($templatePathAndFilename), $parsedTemplate);
		}
		$this->localSyntaxTreeCache[$templatePathAndFilename] = $parsedTemplate;
		return $parsedTemplate;
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
			$actionName = ($this->actionName !== NULL ? $this->actionName : $this->request->getControllerActionName());
			preg_match('/^F3\\\\\w*\\\\(?:(?P<SubpackageName>.*)\\\\)?Controller\\\\(?P<ControllerName>\w*)Controller$/', $this->request->getControllerObjectName(), $matches);
			$subpackageName = '';
			if ($matches['SubpackageName'] !== '') {
				$subpackageName = str_replace('\\', '/', $matches['SubpackageName']);
				$subpackageName .= '/';
			}
			$controllerName = $matches['ControllerName'];
			$templatePathAndFilename = str_replace('@package', $this->packageManager->getPackagePath($this->request->getControllerPackageKey()), $this->templatePathAndFilenamePattern);
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
			$layoutPathAndFilename = str_replace('@package', $this->packageManager->getPackagePath($this->request->getControllerPackageKey()), $this->layoutPathAndFilenamePattern);
			$layoutPathAndFilename = str_replace('@layout', $layoutName, $layoutPathAndFilename);
			return $layoutPathAndFilename;
		}
	}
}
?>
