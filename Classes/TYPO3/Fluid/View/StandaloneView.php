<?php
namespace TYPO3\Fluid\View;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\Arguments;
use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\Flow\Utility\Files;
use TYPO3\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * A standalone template view.
 * Helpful if you want to use Fluid separately from MVC
 * E.g. to generate template based emails.
 *
 * @api
 */
class StandaloneView extends AbstractTemplateView {

	/**
	 * Source code of the Fluid template
	 * @var string
	 */
	protected $templateSource = NULL;

	/**
	 * absolute path of the Fluid template
	 * @var string
	 */
	protected $templatePathAndFilename = NULL;

	/**
	 * absolute root path of the folder that contains Fluid layouts
	 * @var string
	 */
	protected $layoutRootPath = NULL;

	/**
	 * absolute root path of the folder that contains Fluid partials
	 * @var string
	 */
	protected $partialRootPath = NULL;

	/**
	 * @var \TYPO3\Fluid\Core\Compiler\TemplateCompiler
	 * @Flow\Inject
	 */
	protected $templateCompiler;

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 * @Flow\Inject
	 */
	protected $environment;

	/**
	 * @var \TYPO3\Flow\Mvc\FlashMessageContainer
	 * @Flow\Inject
	 */
	protected $flashMessageContainer;

	/**
	 * @var ActionRequest
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param ActionRequest $request The current action request. If none is specified it will be created from the environment.
	 */
	public function __construct(ActionRequest $request = NULL) {
		$this->request = $request;
	}

	/**
	 * Initiates the StandaloneView by creating the required ControllerContext
	 *
	 * @return void
	 */
	public function initializeObject() {
		if ($this->request === NULL) {
			$httpRequest = Request::createFromEnvironment();
			$this->request = $this->objectManager->get('TYPO3\Flow\Mvc\ActionRequest', $httpRequest);
		}

		$uriBuilder = new UriBuilder();
		$uriBuilder->setRequest($this->request);

		$this->setControllerContext(new ControllerContext(
			$this->request,
			new Response(),
			new Arguments(array()),
			$uriBuilder,
			$this->flashMessageContainer
		));
	}

	/**
	 * Sets the format of the current request (default format is "html")
	 *
	 * @param string $format
	 * @return void
	 * @api
	 */
	public function setFormat($format) {
		$this->getRequest()->setFormat($format);
	}

	/**
	 * Returns the format of the current request (default is "html")
	 *
	 * @return string $format
	 * @api
	 */
	public function getFormat() {
		return $this->getRequest()->getFormat();
	}

	/**
	 * Returns the current request object
	 *
	 * @return ActionRequest
	 */
	public function getRequest() {
		return $this->controllerContext->getRequest();
	}

	/**
	 * Sets the absolute path to a Fluid template file
	 *
	 * @param string $templatePathAndFilename Fluid template path
	 * @return void
	 * @api
	 */
	public function setTemplatePathAndFilename($templatePathAndFilename) {
		$this->templatePathAndFilename = $templatePathAndFilename;
	}

	/**
	 * Returns the absolute path to a Fluid template file if it was specified with setTemplatePathAndFilename() before
	 *
	 * @return string Fluid template path
	 * @api
	 */
	public function getTemplatePathAndFilename() {
		return $this->templatePathAndFilename;
	}

	/**
	 * Sets the Fluid template source
	 * You can use setTemplatePathAndFilename() alternatively if you only want to specify the template path
	 *
	 * @param string $templateSource Fluid template source code
	 * @return void
	 * @api
	 */
	public function setTemplateSource($templateSource) {
		$this->templateSource = $templateSource;
	}

	/**
	 * Sets the absolute path to the folder that contains Fluid layout files
	 *
	 * @param string $layoutRootPath Fluid layout root path
	 * @return void
	 * @api
	 */
	public function setLayoutRootPath($layoutRootPath) {
		$this->layoutRootPath = $layoutRootPath;
	}

	/**
	 * Returns the absolute path to the folder that contains Fluid layout files
	 *
	 * @return string Fluid layout root path
	 * @throws Exception\InvalidTemplateResourceException
	 * @api
	 */
	public function getLayoutRootPath() {
		if ($this->layoutRootPath === NULL && $this->templatePathAndFilename === NULL) {
			throw new Exception\InvalidTemplateResourceException('No layout root path has been specified. Use setLayoutRootPath().', 1288091419);
		}
		if ($this->layoutRootPath === NULL) {
			$this->layoutRootPath = dirname($this->templatePathAndFilename) . '/Layouts';
		}
		return $this->layoutRootPath;
	}

	/**
	 * Sets the absolute path to the folder that contains Fluid partial files.
	 *
	 * @param string $partialRootPath Fluid partial root path
	 * @return void
	 * @api
	 */
	public function setPartialRootPath($partialRootPath) {
		$this->partialRootPath = $partialRootPath;
	}

	/**
	 * Returns the absolute path to the folder that contains Fluid partial files
	 *
	 * @return string Fluid partial root path
	 * @throws Exception\InvalidTemplateResourceException
	 * @api
	 */
	public function getPartialRootPath() {
		if ($this->partialRootPath === NULL && $this->templatePathAndFilename === NULL) {
			throw new Exception\InvalidTemplateResourceException('No partial root path has been specified. Use setPartialRootPath().', 1288094511);
		}
		if ($this->partialRootPath === NULL) {
			$this->partialRootPath = dirname($this->templatePathAndFilename) . '/Partials';
		}
		return $this->partialRootPath;
	}

	/**
	 * Checks whether a template can be resolved for the current request
	 *
	 * @return boolean
	 * @api
	 */
	public function hasTemplate() {
		try {
			$this->getTemplateSource();
			return TRUE;
		} catch (Exception\InvalidTemplateResourceException $e) {
			return FALSE;
		}
	}

	/**
	 * Returns a unique identifier for the resolved template file
	 * This identifier is based on the template path and last modification date
	 *
	 * @param string $actionName Name of the action. This argument is not used in this view!
	 * @return string template identifier
	 * @throws Exception\InvalidTemplateResourceException
	 */
	protected function getTemplateIdentifier($actionName = NULL) {
		if ($this->templateSource === NULL) {
			$templatePathAndFilename = $this->getTemplatePathAndFilename();
			if ($templatePathAndFilename === NULL) {
				throw new InvalidTemplateResourceException('Neither TemplateSource nor TemplatePathAndFilename have been specified', 1327431077);
			} elseif (!is_file($templatePathAndFilename)) {
				throw new InvalidTemplateResourceException(sprintf('Template file "%s" could not be loaded', $templatePathAndFilename), 1327431091);
			}
			$templatePathAndFilenameInfo = pathinfo($templatePathAndFilename);
			$templateFilenameWithoutExtension = basename($templatePathAndFilename, '.' . $templatePathAndFilenameInfo['extension']);
			$prefix = sprintf('template_file_%s', $templateFilenameWithoutExtension);
			return $this->createIdentifierForFile($templatePathAndFilename, $prefix);
		} else {
			$templateSource = $this->getTemplateSource();
			$prefix = 'template_source';
			$templateIdentifier = sprintf('Standalone_%s_%s', $prefix, sha1($templateSource));
			return $templateIdentifier;
		}
	}

	/**
	 * Returns the Fluid template source code
	 *
	 * @param string $actionName Name of the action. This argument is not used in this view!
	 * @return string Fluid template source
	 * @throws Exception\InvalidTemplateResourceException
	 */
	protected function getTemplateSource($actionName = NULL) {
		if ($this->templateSource === NULL && $this->templatePathAndFilename === NULL) {
			throw new Exception\InvalidTemplateResourceException('No template has been specified. Use either setTemplateSource() or setTemplatePathAndFilename().', 1288085266);
		}
		if ($this->templateSource === NULL) {
			if (!is_file($this->templatePathAndFilename)) {
				throw new Exception\InvalidTemplateResourceException('Template could not be found at "' . $this->templatePathAndFilename . '".', 1288087061);
			}
			$this->templateSource = file_get_contents($this->templatePathAndFilename);
		}
		return $this->templateSource;
	}

	/**
	 * Returns a unique identifier for the resolved layout file.
	 * This identifier is based on the template path and last modification date
	 *
	 * @param string $layoutName The name of the layout
	 * @return string layout identifier
	 */
	protected function getLayoutIdentifier($layoutName = 'Default') {
		$layoutPathAndFilename = $this->getLayoutPathAndFilename($layoutName);
		$prefix = 'layout_' . $layoutName;
		return $this->createIdentifierForFile($layoutPathAndFilename, $prefix);
	}

	/**
	 * Resolves the path and file name of the layout file, based on
	 * $this->getLayoutRootPath() and request format and returns the file contents
	 *
	 * @param string $layoutName Name of the layout to use. If none given, use "Default"
	 * @return string contents of the layout file if it was found
	 * @throws Exception\InvalidTemplateResourceException
	 */
	protected function getLayoutSource($layoutName = 'Default') {
		$layoutPathAndFilename = $this->getLayoutPathAndFilename($layoutName);
		$layoutSource = file_get_contents($layoutPathAndFilename);
		if ($layoutSource === FALSE) {
			throw new Exception\InvalidTemplateResourceException('"' . $layoutPathAndFilename . '" is not a valid template resource URI.', 1312215888);
		}
		return $layoutSource;
	}

	/**
	 * Resolve the path and file name of the layout file, based on
	 * $this->getLayoutRootPath() and request format
	 *
	 * In case a layout has already been set with setLayoutPathAndFilename(),
	 * this method returns that path, otherwise a path and filename will be
	 * resolved using the layoutPathAndFilenamePattern.
	 *
	 * @param string $layoutName Name of the layout to use. If none given, use "Default"
	 * @return string Path and filename of layout files
	 * @throws Exception\InvalidTemplateResourceException
	 */
	protected function getLayoutPathAndFilename($layoutName = 'Default') {
		$layoutRootPath = $this->getLayoutRootPath();
		if (!is_dir($layoutRootPath)) {
			throw new Exception\InvalidTemplateResourceException('Layout root path "' . $layoutRootPath . '" does not exist.', 1288092521);
		}
		$possibleLayoutPaths = array();
		$possibleLayoutPaths[] = Files::getUnixStylePath($layoutRootPath . '/' . $layoutName . '.' . $this->getRequest()->getFormat());
		$possibleLayoutPaths[] = Files::getUnixStylePath($layoutRootPath . '/' . $layoutName);
		foreach ($possibleLayoutPaths as $layoutPathAndFilename) {
			if (is_file($layoutPathAndFilename)) {
				return $layoutPathAndFilename;
			}
		}
		throw new Exception\InvalidTemplateResourceException('Could not load layout file. Tried following paths: "' . implode('", "', $possibleLayoutPaths) . '".', 1288092555);
	}

	/**
	 * Returns a unique identifier for the resolved partial file.
	 * This identifier is based on the template path and last modification date
	 *
	 * @param string $partialName The name of the partial
	 * @return string partial identifier
	 */
	protected function getPartialIdentifier($partialName) {
		$partialPathAndFilename = $this->getPartialPathAndFilename($partialName);
		$prefix = 'partial_' . $partialName;
		return $this->createIdentifierForFile($partialPathAndFilename, $prefix);
	}

	/**
	 * Resolves the path and file name of the partial file, based on
	 * $this->getPartialRootPath() and request format and returns the file contents
	 *
	 * @param string $partialName The name of the partial
	 * @return string contents of the layout file if it was found
	 * @throws Exception\InvalidTemplateResourceException
	 */
	protected function getPartialSource($partialName) {
		$partialPathAndFilename = $this->getPartialPathAndFilename($partialName);
		$partialSource = file_get_contents($partialPathAndFilename);
		if ($partialSource === FALSE) {
			throw new Exception\InvalidTemplateResourceException('"' . $partialPathAndFilename . '" is not a valid template resource URI.', 1257246929);
		}
		return $partialSource;
	}

	/**
	 * Resolve the partial path and filename based on $this->getPartialRootPath() and request format
	 *
	 * @param string $partialName The name of the partial
	 * @return string the full path which should be used. The path definitely exists.
	 * @throws Exception\InvalidTemplateResourceException
	 */
	protected function getPartialPathAndFilename($partialName) {
		$partialRootPath = $this->getPartialRootPath();
		if (!is_dir($partialRootPath)) {
			throw new Exception\InvalidTemplateResourceException('Partial root path "' . $partialRootPath . '" does not exist.', 1288094648);
		}
		$possiblePartialPaths = array();
		$possiblePartialPaths[] = Files::getUnixStylePath($partialRootPath . '/' . $partialName . '.' . $this->getRequest()->getFormat());
		$possiblePartialPaths[] = Files::getUnixStylePath($partialRootPath . '/' . $partialName);
		foreach ($possiblePartialPaths as $partialPathAndFilename) {
			if (is_file($partialPathAndFilename)) {
				return $partialPathAndFilename;
			}
		}
		throw new Exception\InvalidTemplateResourceException('Could not load partial file. Tried following paths: "' . implode('", "', $possiblePartialPaths) . '".', 1288092555);
	}

	/**
	 * Returns a unique identifier for the given file in the format
	 * Standalone_<prefix>_<SHA1>
	 * The SHA1 hash is a checksum that is based on the file path and last modification date
	 *
	 * @param string $pathAndFilename
	 * @param string $prefix
	 * @return string
	 */
	protected function createIdentifierForFile($pathAndFilename, $prefix) {
		$templateModifiedTimestamp = filemtime($pathAndFilename);
		$templateIdentifier = sprintf('Standalone_%s_%s', $prefix, sha1($pathAndFilename . '|' . $templateModifiedTimestamp));
		$templateIdentifier = str_replace('/', '_', str_replace('.', '_', $templateIdentifier));
		return $templateIdentifier;
	}
}
