<?php
namespace TYPO3\Fluid\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\Fluid\Core\Parser\Configuration;
use TYPO3\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3\Fluid\Core\Parser\Interceptor\Resource;
use TYPO3\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\Parser\TemplateParser;
use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\Fluid\View\Exception\InvalidSectionException;

/**
 * Abstract Fluid Template View.
 *
 * Contains the fundamental methods which any Fluid based template view needs.
 */
abstract class AbstractTemplateView extends AbstractView {

	/**
	 * Constants defining possible rendering types
	 */
	const RENDERING_TEMPLATE = 1;
	const RENDERING_PARTIAL = 2;
	const RENDERING_LAYOUT = 3;

	/**
	 * @var TemplatePaths
	 */
	protected $templatePaths;

	/**
	 * @var TemplateParser
	 */
	protected $templateParser;

	/**
	 * @var TemplateCompiler
	 */
	protected $templateCompiler;

	/**
	 * The initial rendering context for this template view.
	 * Due to the rendering stack, another rendering context might be active
	 * at certain points while rendering the template.
	 *
	 * @var RenderingContextInterface
	 */
	protected $baseRenderingContext;

	/**
	 * Stack containing the current rendering type, the current rendering context, and the current parsed template
	 * Do not manipulate directly, instead use the methods"getCurrent*()", "startRendering(...)" and "stopRendering()"
	 *
	 * @var array
	 */
	protected $renderingStack = array();

	/**
	 * Partial Name -> Partial Identifier cache.
	 * This is a performance optimization, effective when rendering a
	 * single partial many times.
	 *
	 * @var array
	 */
	protected $partialIdentifierCache = array();

	/**
	 * @var ViewHelperResolver
	 */
	protected $viewHelperResolver;

	/**
	 * Constructor
	 *
	 * @param TemplatePaths $paths
	 * @param RenderingContext $context
	 * @param FluidCacheInterface $cache
	 */
	public function __construct(TemplatePaths $paths, RenderingContext $context = NULL, FluidCacheInterface $cache = NULL) {
		if (!$context) {
			$context = new RenderingContext();
			$context->setControllerName('Default');
			$context->setControllerAction('Default');
			$context->setVariableProvider(new StandardVariableProvider($this->variables));
			$context->injectViewHelperVariableContainer(new ViewHelperVariableContainer());
		}
		$this->templatePaths = $paths;
		$this->viewHelperResolver = new ViewHelperResolver();
		$this->setRenderingContext($context);
		$this->setTemplateCompiler(new TemplateCompiler($this->viewHelperResolver));
		$this->setTemplateParser(new TemplateParser($this->viewHelperResolver));
		$this->templateCompiler->setTemplateCache($cache);
	}

	/**
	 * @return RenderingContextInterface
	 */
	public function getRenderingContext() {
		return $this->baseRenderingContext;
	}

	/**
	 * @return TemplatePaths
	 */
	public function getTemplatePaths() {
		return $this->templatePaths;
	}

	/**
	 * @param ViewHelperResolver $viewHelperResolver
	 * @return void
	 */
	public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver) {
		$this->viewHelperResolver = $viewHelperResolver;
		$this->templateParser->setViewHelperResolver($viewHelperResolver);
		$this->templateCompiler->setViewHelperResolver($viewHelperResolver);
	}

	/**
	 * Inject the Template Parser
	 *
	 * @param TemplateParser $templateParser The template parser
	 * @return void
	 */
	public function setTemplateParser(TemplateParser $templateParser) {
		$this->templateParser = $templateParser;
	}

	/**
	 * @param TemplateCompiler $templateCompiler
	 * @return void
	 */
	public function setTemplateCompiler(TemplateCompiler $templateCompiler) {
		$this->templateCompiler = $templateCompiler;
	}

	/**
	 * Injects a fresh rendering context
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return void
	 */
	public function setRenderingContext(RenderingContextInterface $renderingContext) {
		$this->baseRenderingContext = $renderingContext;
		$this->baseRenderingContext->getViewHelperVariableContainer()->setView($this);
	}

	/**
	 * Set the cache used by this View.
	 *
	 * @param FluidCacheInterface $cache
	 * @return void
	 */
	public function setCache(FluidCacheInterface $cache) {
		$this->templateCompiler->setTemplateCache($cache);
	}

	/**
	 * Assign a value to the variable container.
	 *
	 * @param string $key The key of a view variable to set
	 * @param mixed $value The value of the view variable
	 * @return $this
	 * @api
	 */
	public function assign($key, $value) {
		$templateVariableContainer = $this->baseRenderingContext->getVariableProvider();
		if ($templateVariableContainer->exists($key)) {
			$templateVariableContainer->remove($key);
		}
		$templateVariableContainer->add($key, $value);
		return $this;
	}

	/**
	 * Assigns multiple values to the JSON output.
	 * However, only the key "value" is accepted.
	 *
	 * @param array $values Keys and values - only a value with key "value" is considered
	 * @return $this
	 * @api
	 */
	public function assignMultiple(array $values) {
		$templateVariableContainer = $this->baseRenderingContext->getVariableProvider();
		foreach ($values as $key => $value) {
			if ($templateVariableContainer->exists($key)) {
				$templateVariableContainer->remove($key);
			}
			$templateVariableContainer->add($key, $value);
		}
		return $this;
	}

	/**
	 * Loads the template source and render the template.
	 * If "layoutName" is set in a PostParseFacet callback, it will render the file with the given layout.
	 *
	 * @param string $actionName If set, this action's template will be rendered instead of the one defined in the context.
	 * @return string Rendered Template
	 * @api
	 */
	public function render($actionName = NULL) {
		$this->templateParser->setConfiguration($this->buildParserConfiguration());
		$controllerName = $this->baseRenderingContext->getControllerName();
		if (!$actionName) {
			$actionName = $this->baseRenderingContext->getControllerAction();
		}
		$actionName = ucfirst($actionName);
		if (empty($templateIdentifier)) {
			$templateIdentifier = $this->templatePaths->getTemplateIdentifier($controllerName, $actionName);
		}
		$parsedTemplate = $this->getOrParseAndStoreTemplate(
			$templateIdentifier,
			function ($parent, TemplatePaths $paths) use ($controllerName, $actionName) {
				return $paths->getTemplateSource($controllerName, $actionName);
			}
		);

		if (!$parsedTemplate->hasLayout()) {
			$this->startRendering(self::RENDERING_TEMPLATE, $parsedTemplate, $this->baseRenderingContext);
			$output = $parsedTemplate->render($this->baseRenderingContext);
			$this->stopRendering();
		} else {
			$layoutName = $parsedTemplate->getLayoutName($this->baseRenderingContext);
			$layoutIdentifier = $this->templatePaths->getLayoutIdentifier($layoutName);
			$parsedLayout = $this->getOrParseAndStoreTemplate(
				$layoutIdentifier,
				function($parent, TemplatePaths $paths) use ($layoutName) {
					return $paths->getLayoutSource($layoutName);
				}
			);
			$this->startRendering(self::RENDERING_LAYOUT, $parsedTemplate, $this->baseRenderingContext);
			$output = $parsedLayout->render($this->baseRenderingContext);
			$this->stopRendering();
		}

		return $output;
	}

	/**
	 * @param string $templateIdentifier
	 * @param \Closure $templateSourceClosure Closure which returns the template source if needed
	 * @return ParsedTemplateInterface
	 */
	protected function getOrParseAndStoreTemplate($templateIdentifier, $templateSourceClosure) {
		if ($this->templateCompiler->has($templateIdentifier)) {
			$parsedTemplate = $this->templateCompiler->get($templateIdentifier);
		} else {
			$parsedTemplate = $this->templateParser->parse($templateSourceClosure($this, $this->templatePaths));
			if ($parsedTemplate->isCompilable()) {
				$this->templateCompiler->store($templateIdentifier, $parsedTemplate);
			}
		}
		return $parsedTemplate;
	}

	/**
	 * Renders a given section.
	 *
	 * @param string $sectionName Name of section to render
	 * @param array $variables The variables to use
	 * @param boolean $ignoreUnknown Ignore an unknown section and just return an empty string
	 * @return string rendered template for the section
	 * @throws InvalidSectionException
	 */
	public function renderSection($sectionName, array $variables = NULL, $ignoreUnknown = FALSE) {
		$renderingContext = $this->getCurrentRenderingContext();

		if ($this->getCurrentRenderingType() === self::RENDERING_LAYOUT) {
			// in case we render a layout right now, we will render a section inside a TEMPLATE.
			$renderingTypeOnNextLevel = self::RENDERING_TEMPLATE;
		} else {
			$renderingContext = clone $renderingContext;
			$renderingContext->setVariableProvider(new StandardVariableProvider($variables));
			$renderingTypeOnNextLevel = $this->getCurrentRenderingType();
		}

		$parsedTemplate = $this->getCurrentParsedTemplate();

		if ($parsedTemplate->isCompiled()) {
			$methodNameOfSection = 'section_' . sha1($sectionName);
			if (!method_exists($parsedTemplate, $methodNameOfSection)) {
				if ($ignoreUnknown) {
					return '';
				} else {
					throw new InvalidSectionException('Section "' . $sectionName . '" does not exist.');
				}
			}
			$this->startRendering($renderingTypeOnNextLevel, $parsedTemplate, $renderingContext);
			$output = $parsedTemplate->$methodNameOfSection($renderingContext);
			$this->stopRendering();
		} else {
			$sections = $parsedTemplate->getVariableContainer()->get('sections');
			if (!isset($sections[$sectionName])) {
				if ($ignoreUnknown) {
					return '';
				}
				throw new InvalidSectionException('Section "' . $sectionName . '" does not exist.');
			}
			/** @var $section ViewHelperNode */
			$section = $sections[$sectionName];

			$renderingContext->getViewHelperVariableContainer()->add(
				'TYPO3\Fluid\ViewHelpers\SectionViewHelper',
				'isCurrentlyRenderingSection',
				'TRUE'
			);

			$this->startRendering($renderingTypeOnNextLevel, $parsedTemplate, $renderingContext);
			$output = $section->evaluate($renderingContext);
			$this->stopRendering();
		}

		return $output;
	}

	/**
	 * Renders a partial.
	 *
	 * @param string $partialName
	 * @param string $sectionName
	 * @param array $variables
	 * @param boolean $ignoreUnknown Ignore an unknown section and just return an empty string
	 * @return string
	 */
	public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = FALSE) {
		if (!isset($this->partialIdentifierCache[$partialName])) {
			$this->partialIdentifierCache[$partialName] = $this->templatePaths->getPartialIdentifier($partialName);
		}
		$partialIdentifier = $this->partialIdentifierCache[$partialName];
		$parsedPartial = $this->getOrParseAndStoreTemplate(
			$partialIdentifier,
			function ($parent, TemplatePaths $paths) use ($partialName) {
				return $paths->getPartialSource($partialName);
			}
		);
		$variableContainer = new StandardVariableProvider($variables);
		$renderingContext = clone $this->getCurrentRenderingContext();
		$renderingContext->setVariableProvider($variableContainer);
		$this->startRendering(self::RENDERING_PARTIAL, $parsedPartial, $renderingContext);
		if ($sectionName !== NULL) {
			$output = $this->renderSection($sectionName, $variables, $ignoreUnknown);
		} else {
			$output = $parsedPartial->render($renderingContext);
		}
		$this->stopRendering();
		return $output;
	}

	/**
	 * Build parser configuration
	 *
	 * @return Configuration
	 */
	protected function buildParserConfiguration() {
		$parserConfiguration = new Configuration();
		$escapeInterceptor = new Escape();
		$parserConfiguration->addEscapingInterceptor($escapeInterceptor);
		return $parserConfiguration;
	}

	/**
	 * Start a new nested rendering. Pushes the given information onto the $renderingStack.
	 *
	 * @param integer $type one of the RENDERING_* constants
	 * @param ParsedTemplateInterface $template
	 * @param RenderingContextInterface $context
	 * @return void
	 */
	protected function startRendering($type, ParsedTemplateInterface $template, RenderingContextInterface $context) {
		array_push($this->renderingStack, array('type' => $type, 'parsedTemplate' => $template, 'renderingContext' => $context));
	}

	/**
	 * Stops the current rendering. Removes one element from the $renderingStack. Make sure to always call this
	 * method pair-wise with startRendering().
	 *
	 * @return void
	 */
	protected function stopRendering() {
		array_pop($this->renderingStack);
	}

	/**
	 * Get the current rendering type.
	 *
	 * @return integer one of RENDERING_* constants
	 */
	protected function getCurrentRenderingType() {
		$currentRendering = end($this->renderingStack);
		return $currentRendering['type'];
	}

	/**
	 * Get the parsed template which is currently being rendered.
	 *
	 * @return ParsedTemplateInterface
	 */
	protected function getCurrentParsedTemplate() {
		$currentRendering = end($this->renderingStack);
		return $currentRendering['parsedTemplate'];
	}

	/**
	 * Get the rendering context which is currently used.
	 *
	 * @return RenderingContextInterface
	 */
	protected function getCurrentRenderingContext() {
		$currentRendering = end($this->renderingStack);
		return $currentRendering['renderingContext'];
	}

}
