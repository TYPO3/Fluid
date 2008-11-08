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
 * @package 
 * @subpackage 
 * @version $Id:$
 */

class TemplateView extends F3::FLOW3::MVC::View::AbstractView {
	protected $templatePattern = '@packageResources/Template/@controller/@action.xhtml';
	
	/**
	 * Template parser instance.
	 * @var F3::Beer3::TemplateParser
	 */
	protected $templateParser;
	
	public function injectTemplateParser(F3::Beer3::TemplateParser $templateParser) {
		$this->templateParser = $templateParser;
	}
	public function initializeView() {	
	}
	
	public function render() {
		$templatePath = $this->templatePattern;
		$templatePath = str_replace('@package', $this->packageManager->getPackagePath($this->request->getControllerPackageKey()), $templatePath);
		$templatePath = str_replace('@controller', $this->request->getControllerName(), $templatePath);
		$templatePath = str_replace('@action', $this->request->getControllerActionName(), $templatePath);

		$templateSource = file_get_contents($templatePath, FILE_TEXT);
		if (!$templateSource) throw new F3::Beer3::RuntimeException('The template file "' . $templatePath . '" was not found.', 1225709595);
		$templateTree = $this->templateParser->parse($templateSource);
		// TODO
		$variableStore = $this->componentFactory->getComponent('F3::Beer3::VariableContainer');
		$result = $templateTree->render($variableStore);
		return $result;
	}
}


?>