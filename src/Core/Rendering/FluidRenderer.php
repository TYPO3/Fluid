<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\PassthroughSourceException;
use TYPO3Fluid\Fluid\View\Exception;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\TemplatePaths;

class FluidRenderer
{
    /**
     * Constants defining possible rendering types
     */
    const RENDERING_TEMPLATE = 1;
    const RENDERING_PARTIAL = 2;
    const RENDERING_LAYOUT = 3;

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
    protected $renderingStack = [];

    /**
     * @var callable|null
     * @deprecated Will be removed in Fluid 4.0
     */
    protected $baseTemplateClosure;

    /**
     * @var callable|null
     * @deprecated Will be removed in Fluid 4.0
     */
    protected $baseIdentifierClosure;

    public function __construct(RenderingContextInterface $renderingContext)
    {
        $this->baseRenderingContext = $renderingContext;
    }

    /**
     * @param callable|null $baseTemplateClosure
     * @return FluidRenderer
     * @deprecated Will be removed in Fluid 4.0
     */
    public function setBaseTemplateClosure(?callable $baseTemplateClosure): self
    {
        $this->baseTemplateClosure = $baseTemplateClosure;
        return $this;
    }

    /**
     * @param callable|null $baseIdentifierClosure
     * @return FluidRenderer
     * @deprecated Will be removed in Fluid 4.0
     */
    public function setBaseIdentifierClosure(?callable $baseIdentifierClosure): self
    {
        $this->baseIdentifierClosure = $baseIdentifierClosure;
        return $this;
    }

    public function getRenderingContext(): RenderingContextInterface
    {
        return $this->baseRenderingContext;
    }

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->baseRenderingContext = $renderingContext;
    }

    public function renderSource(string $source)
    {
        $renderingContext = $this->getCurrentRenderingContext();
        $renderingContext->getTemplatePaths()->setTemplateSource($source);
        $templateParser = $renderingContext->getTemplateParser();
        $templatePaths = $renderingContext->getTemplatePaths();
        try {
            $parsedTemplate = $templateParser->getOrParseAndStoreTemplate(
                sha1($source),
                function($parent, TemplatePaths $paths) use ($source): string { return $source; }
            );
            $parsedTemplate->getArguments()->setRenderingContext($renderingContext);
        } catch (PassthroughSourceException $error) {
            return $error->getSource();
        }

        try {
            $layoutNameNode = $parsedTemplate->getNamedChild('layoutName');
            $layoutName = $layoutNameNode->getArguments()->setRenderingContext($renderingContext)['name'];
        } catch (ChildNotFoundException $exception) {
            $layoutName = null;
        }

        if ($layoutName) {
            try {
                $parsedLayout = $templateParser->getOrParseAndStoreTemplate(
                    $templatePaths->getLayoutIdentifier($layoutName),
                    function($parent, TemplatePaths $paths) use ($layoutName): string {
                        return $paths->getLayoutSource($layoutName);
                    }
                );
                $parsedLayout->getArguments()->setRenderingContext($renderingContext);
            } catch (PassthroughSourceException $error) {
                return $error->getSource();
            }
            $this->startRendering(self::RENDERING_LAYOUT, $parsedTemplate, $this->baseRenderingContext);
            $output = $parsedLayout->evaluate($this->baseRenderingContext);
            $this->stopRendering();
        } else {
            $this->startRendering(self::RENDERING_TEMPLATE, $parsedTemplate, $this->baseRenderingContext);
            $output = $parsedTemplate->evaluate($this->baseRenderingContext);
            $this->stopRendering();
        }
        return $output;
    }

    /**
     * Loads the template source and render the template.
     * If "layoutName" is set in a PostParseFacet callback, it will render the file with the given layout.
     *
     * @param string $filePathAndName
     * @return mixed Rendered Template
     */
    public function renderFile(string $filePathAndName)
    {
        $this->baseRenderingContext->getTemplatePaths()->setTemplatePathAndFilename($filePathAndName);
        $output = $this->renderSource(file_get_contents($filePathAndName));
        $this->baseRenderingContext->getTemplatePaths()->setTemplatePathAndFilename(null);
        return $output;
    }

    /**
     * Renders a given section.
     *
     * Deprecated in favor of the Atoms concept which can be accessed through the ViewHelperResolver.
     *
     * A section can be rendered by resolving the appropriate (template, layout or partial-like)
     * Atom and using either getTypedChildren() or getNamedChild() to extract the desired section
     * and render it via the Component interface the return value implements.
     *
     * @param string $sectionName Name of section to render
     * @param array $variables The variables to use
     * @param boolean $ignoreUnknown Ignore an unknown section and just return an empty string
     * @return mixed rendered template for the section
     * @throws ChildNotFoundException
     * @throws InvalidTemplateResourceException
     * @throws Exception
     * @deprecated Will be removed in Fluid 4.0
     */
    public function renderSection(string $sectionName, array $variables = [], bool $ignoreUnknown = false)
    {
        if ($this->getCurrentRenderingType() === self::RENDERING_LAYOUT) {
            // in case we render a layout right now, we will render a section inside a TEMPLATE.
            $renderingTypeOnNextLevel = self::RENDERING_TEMPLATE;
            $renderingContext = $this->getCurrentRenderingContext();
        } else {
            $renderingTypeOnNextLevel = $this->getCurrentRenderingType();
            $renderingContext = clone $this->getCurrentRenderingContext();
            $renderingContext->setVariableProvider($renderingContext->getVariableProvider()->getScopeCopy($variables));
        }

        try {
            $parsedTemplate = $this->getCurrentParsedTemplate();
        } catch (PassthroughSourceException $error) {
            return $error->getSource();
        } catch (InvalidTemplateResourceException $error) {
            if (!$ignoreUnknown) {
                return $renderingContext->getErrorHandler()->handleViewError($error);
            }
            return '';
        } catch (Exception $error) {
            return $renderingContext->getErrorHandler()->handleViewError($error);
        }

        try {
            $section = $parsedTemplate->getNamedChild($sectionName);
        } catch (ChildNotFoundException $exception) {
            if (!$ignoreUnknown) {
                return $renderingContext->getErrorHandler()->handleViewError($exception);
            }
            return '';
        }

        $this->startRendering($renderingTypeOnNextLevel, $parsedTemplate, $renderingContext);
        $output = $section->evaluate($renderingContext);
        $this->stopRendering();

        return $output;
    }

    /**
     * Renders a partial.
     *
     * Deprecated in favor of Atoms concept which can be accessed through the
     * ViewHelperResolver to fetch and render a (partial-like) Atom directly.
     *
     * @param string $partialName
     * @param string|null $sectionName
     * @param array $variables
     * @param boolean $ignoreUnknown Ignore an unknown section and just return an empty string
     * @return mixed
     * @throws ChildNotFoundException
     * @throws InvalidTemplateResourceException
     * @throws Exception
     * @deprecated Will be removed in Fluid 4.0
     */
    public function renderPartial(string $partialName, ?string $sectionName, array $variables, bool $ignoreUnknown = false)
    {
        $templatePaths = $this->baseRenderingContext->getTemplatePaths();
        $renderingContext = clone $this->getCurrentRenderingContext();
        try {
            $parsedPartial = $renderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
                $templatePaths->getPartialIdentifier($partialName),
                function ($parent, TemplatePaths $paths) use ($partialName): string {
                    return $paths->getPartialSource($partialName);
                }
            );
            $parsedPartial->getArguments()->setRenderingContext($renderingContext);
        } catch (PassthroughSourceException $error) {
            return $error->getSource();
        } catch (InvalidTemplateResourceException $error) {
            if (!$ignoreUnknown) {
                return $renderingContext->getErrorHandler()->handleViewError($error);
            }
            return '';
        } catch (ChildNotFoundException $error) {
            if (!$ignoreUnknown) {
                return $renderingContext->getErrorHandler()->handleViewError($error);
            }
            return '';
        } catch (Exception $error) {
            return $renderingContext->getErrorHandler()->handleViewError($error);
        }
        $this->startRendering(self::RENDERING_PARTIAL, $parsedPartial, $renderingContext);
        if ($sectionName !== null) {
            $output = $this->renderSection($sectionName, $variables, $ignoreUnknown);
        } else {
            $renderingContext->setVariableProvider($renderingContext->getVariableProvider()->getScopeCopy($variables));
            $output = $parsedPartial->evaluate($renderingContext);
        }
        $this->stopRendering();
        return $output;
    }

    /**
     * Start a new nested rendering. Pushes the given information onto the $renderingStack.
     *
     * @param integer $type one of the RENDERING_* constants
     * @param ComponentInterface $template
     * @param RenderingContextInterface $context
     * @return void
     */
    protected function startRendering(int $type, ComponentInterface $template, RenderingContextInterface $context): void
    {
        array_push($this->renderingStack, ['type' => $type, 'parsedTemplate' => $template, 'renderingContext' => $context]);
    }

    /**
     * Stops the current rendering. Removes one element from the $renderingStack. Make sure to always call this
     * method pair-wise with startRendering().
     *
     * @return void
     */
    protected function stopRendering(): void
    {
        array_pop($this->renderingStack);
    }

    protected function getCurrentRenderingType(): int
    {
        $currentRendering = end($this->renderingStack);
        return $currentRendering['type'] ? $currentRendering['type'] : self::RENDERING_TEMPLATE;
    }

    protected function getCurrentParsedTemplate(): ComponentInterface
    {
        $currentRendering = end($this->renderingStack);
        $renderingContext = $this->getCurrentRenderingContext();
        $parsedTemplate = $currentRendering['parsedTemplate'] ?? null;
        if ($parsedTemplate) {
            return $parsedTemplate;
        }
        $templatePaths = $renderingContext->getTemplatePaths();
        $templateParser = $renderingContext->getTemplateParser();

        // Retrieve the current parsed template, which happens if the renderSection() method was called as first entry
        // method (as opposed to rendering through renderFile / renderSource which causes stack entries which in turn
        // causes this method to return early).
        // Support for the closures will be removed in Fluid 4.0 since they are a temporary measure.
        $parsedTemplate = $templateParser->getOrParseAndStoreTemplate(
            $this->baseIdentifierClosure ? call_user_func($this->baseIdentifierClosure) : $templatePaths->getTemplateIdentifier('Default', 'Default'),
            $this->baseTemplateClosure ?? function($parent, TemplatePaths $paths): string {
                return $paths->getTemplateSource('Default', 'Default');
            }
        );
        return $parsedTemplate;
    }

    protected function getCurrentRenderingContext(): RenderingContextInterface
    {
        $currentRendering = end($this->renderingStack);
        return $currentRendering['renderingContext'] ? $currentRendering['renderingContext'] : $this->baseRenderingContext;
    }
}