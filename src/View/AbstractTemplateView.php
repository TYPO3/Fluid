<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Abstract Fluid Template View.
 *
 * Contains the fundamental methods which any Fluid based template view needs.
 *
 * @deprecated Will be removed in Fluid 4.0
 */
abstract class AbstractTemplateView extends AbstractView
{
    /**
     * The initial rendering context for this template view.
     * Due to the rendering stack, another rendering context might be active
     * at certain points while rendering the template.
     *
     * @var RenderingContextInterface
     */
    protected $baseRenderingContext;

    public function __construct(RenderingContextInterface $context = null)
    {
        if ($context === null) {
            $context = new RenderingContext();
            if (is_callable([$context, 'setControllerName'])) {
                $context->setControllerName('Default');
            }
            if (is_callable([$context, 'setControllerAction'])) {
                $context->setControllerAction('Default');
            }
        }
        $this->setRenderingContext($context);
    }

    public function getTemplatePaths(): TemplatePaths
    {
        return $this->baseRenderingContext->getTemplatePaths();
    }

    public function getViewHelperResolver(): ViewHelperResolver
    {
        return $this->baseRenderingContext->getViewHelperResolver();
    }

    public function getRenderingContext(): RenderingContextInterface
    {
        return $this->baseRenderingContext;
    }

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->baseRenderingContext = $renderingContext;
        $this->baseRenderingContext->getViewHelperVariableContainer()->setView($this);
        $this->baseRenderingContext->getRenderer()->setRenderingContext($this->baseRenderingContext);
    }

    /**
     * Assign a value to the variable container.
     *
     * @param string $key The key of a view variable to set
     * @param mixed $value The value of the view variable
     * @return $this
     */
    public function assign($key, $value): ViewInterface
    {
        $this->baseRenderingContext->getVariableProvider()->add($key, $value);
        return $this;
    }

    /**
     * Assigns multiple values to the JSON output.
     * However, only the key "value" is accepted.
     *
     * @param array $values Keys and values - only a value with key "value" is considered
     * @return self
     */
    public function assignMultiple(array $values): ViewInterface
    {
        $templateVariableContainer = $this->baseRenderingContext->getVariableProvider();
        foreach ($values as $key => $value) {
            $templateVariableContainer->add($key, $value);
        }
        return $this;
    }

    /**
     * Loads the template source and render the template.
     * If "layoutName" is set in a PostParseFacet callback, it will render the file with the given layout.
     *
     * @param string|null $actionName If set, this action's template will be rendered instead of the one defined in the context.
     * @return mixed Rendered Template
     */
    public function render(?string $actionName = null)
    {
        $renderingContext = $this->baseRenderingContext;
        $templatePaths = $renderingContext->getTemplatePaths();
        if ($actionName) {
            $actionName = ucfirst($actionName);
            if (is_callable([$renderingContext, 'setControllerAction'])) {
                $renderingContext->setControllerAction($actionName);
            }
        } else {
            $actionName = is_callable([$renderingContext, 'getControllerAction']) ? $renderingContext->getControllerAction() : 'Default';
        }
        $controllerName = is_callable([$renderingContext, 'getControllerName']) ? $renderingContext->getControllerName() : 'Default';
        $filePathAndFilename = $templatePaths->resolveTemplateFileForControllerAndActionAndFormat($controllerName, $actionName);

        if ($filePathAndFilename !== null) {
            return $renderingContext->getRenderer()->renderFile($filePathAndFilename);
        }
        return $renderingContext->getRenderer()->renderSource(
            $templatePaths->getTemplateSource($controllerName, $actionName)
        );
    }

    /**
     * Renders a given section.
     *
     * @param string $sectionName Name of section to render
     * @param array $variables The variables to use
     * @param boolean $ignoreUnknown Ignore an unknown section and just return an empty string
     * @return mixed rendered template for the section
     * @throws ChildNotFoundException
     * @throws InvalidTemplateResourceException
     * @throws Exception
     */
    public function renderSection(string $sectionName, array $variables = [], bool $ignoreUnknown = false)
    {
        $context = $this->baseRenderingContext;
        $templatePaths = $context->getTemplatePaths();
        $templateClosure = function(RenderingContextInterface $renderingContext): string {
            return $renderingContext->getTemplatePaths()->getTemplateSource('Default', 'Default');
        };
        $identifierClosure = function() use ($context, $templatePaths) {
            return $templatePaths->getTemplateSource($context->getControllerName(), $context->getControllerAction());
        };
        return $this->baseRenderingContext->getRenderer()
            ->setBaseTemplateClosure($templateClosure)
            ->setBaseIdentifierClosure($identifierClosure)
            ->renderSection($sectionName, $variables, $ignoreUnknown);
    }

    /**
     * Renders a partial.
     *
     * @param string $partialName
     * @param string|null $sectionName
     * @param array $variables
     * @param boolean $ignoreUnknown Ignore an unknown section and just return an empty string
     * @return mixed
     * @throws ChildNotFoundException
     * @throws InvalidTemplateResourceException
     * @throws Exception
     */
    public function renderPartial(string $partialName, ?string $sectionName, array $variables, bool $ignoreUnknown = false)
    {
        return $this->baseRenderingContext->getRenderer()->renderPartial($partialName, $sectionName, $variables, $ignoreUnknown);
    }
}
