<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::View;

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
 * @package Beer3
 * @subpackage View
 * @version $Id:$
 */
/**
 * The main template view. Should be used as view if you want Beer3 Templating
 * 
 * @package Beer3
 * @subpackage View
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TemplateView extends F3::FLOW3::MVC::View::AbstractView {
	protected $templatePathPattern = '@packageResources/Template/@controller/@action.xhtml';
	
	/**
	 * Template parser instance.
	 * @var F3::Beer3::Core::TemplateParser
	 */
	protected $templateParser;
	
	/**
	 * Context variables
	 * @var array of context variables
	 */
	protected $contextVariables = array();
	
	/**
	 * Inject the template parser
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectTemplateParser(F3::Beer3::Core::TemplateParser $templateParser) {
		$this->templateParser = $templateParser;
	}
	
	/**
	 * Initialize view
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeView() {	
	}
	
	/**
	 * Load the template file, expects it at the location specified at $this->templatePathPattern.
	 * 
	 * Override if you have some custom logic saying where the template is.
	 * 
	 * @return string the contents of the template file
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function loadTemplateFile() {
		$templatePath = $this->templatePathPattern;
		$templatePath = str_replace('@package', $this->packageManager->getPackagePath($this->request->getControllerPackageKey()), $templatePath);
		$templatePath = str_replace('@controller', $this->request->getControllerName(), $templatePath);
		$templatePath = str_replace('@action', $this->request->getControllerActionName(), $templatePath);

		$templateSource = file_get_contents($templatePath, FILE_TEXT);
		if (!$templateSource) throw new F3::Beer3::Core::RuntimeException('The template file "' . $templatePath . '" was not found.', 1225709595);
		return $templateSource;
	}
	
	/**
	 * Find the XHTML template according to $this->templatePathPattern and render the template.
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$templateTree = $this->parseTemplate();
		
		$this->contextVariables['view'] = $this;
		
		$variableStore = $this->objectFactory->create('F3::Beer3::Core::VariableContainer', $this->contextVariables);
		$result = $templateTree->render($variableStore);
		return $result;
	}
	
	protected function parseTemplate() {
		$templateSource = $this->loadTemplateFile();
		
		return $this->templateParser->parse($templateSource);	
	}
	
	/**
	 * Add a variable to the context.
	 * Can be chained, so $template->addVariable(..., ...)->addVariable(..., ...); is possible,
	 * 
	 * @param string $key Key of variable
	 * @param object $value Value of object
	 * @return F3::Beer3::View::TemplateView an instance of $this, to enable chaining.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addVariable($key, $value) {
		$this->contextVariables[$key] = $value;
		return $this;
	}
	
	/**
	 * Return the current request
	 * 
	 * @return F3::FLOW3::MVC::Web::Request the current request
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getRequest() {
		return $this->request;
	}
}


?>