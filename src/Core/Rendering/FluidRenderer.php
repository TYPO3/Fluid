<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\PassthroughSourceException;
use TYPO3Fluid\Fluid\View\Exception;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

class FluidRenderer implements FluidRendererInterface
{
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

    public function getComponentBeingRendered(): ?ComponentInterface
    {
        return end($this->renderingStack) ?: null;
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
        $renderingContext = $this->baseRenderingContext;
        $renderingContext->getTemplatePaths()->setTemplateSource($source);
        $templateParser = $renderingContext->getTemplateParser();
        try {
            $parsedTemplate = $templateParser->getOrParseAndStoreTemplate(
                sha1($source),
                function() use ($source): string { return $source; }
            );
            $parsedTemplate->getArguments()
                ->assignAll($renderingContext->getVariableProvider()->getAll())
                ->setRenderingContext($renderingContext)
                ->validate();
        } catch (PassthroughSourceException $error) {
            return $error->getSource();
        }

        $this->renderingStack[] = $parsedTemplate;
        $output = $parsedTemplate->evaluate($this->baseRenderingContext);
        array_pop($this->renderingStack);
        return $output;
    }

    public function renderComponent(ComponentInterface $component)
    {
        $this->renderingStack[] = $component;
        $output = $component->evaluate($this->baseRenderingContext);
        array_pop($this->renderingStack);
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
        return $this->renderSource(file_get_contents($filePathAndName));
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
     */
    public function renderSection(string $sectionName, array $variables = [], bool $ignoreUnknown = false)
    {
        $renderingContext = $this->baseRenderingContext;

        if (empty($variables)) {
            // Rendering a section without variables always assigns all variables. If the section doesn't need variables
            // it will behave no differently - and when calling the section from a layout-like Atom, presence of all
            // variables is assumed without passing any to the f:render statement.
            $variables = $renderingContext->getVariableProvider()->getAll();
        }

        try {
            $parsedTemplate = $this->getCurrentParsedTemplate();
            $section = $parsedTemplate->getNamedChild($sectionName);
            $section->getArguments()->assignAll($variables)->setRenderingContext($renderingContext)->validate();
        } catch (ChildNotFoundException $error) {
            if (!$ignoreUnknown) {
                return $renderingContext->getErrorHandler()->handleViewError($error);
            }
            return '';
        } catch (InvalidTemplateResourceException $error) {
            if (!$ignoreUnknown) {
                return $renderingContext->getErrorHandler()->handleViewError($error);
            }
            return '';
        } catch (Exception $error) {
            return $renderingContext->getErrorHandler()->handleViewError($error);
        }

        $output = $section->evaluate($renderingContext);

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
    public function renderPartial(string $partialName, ?string $sectionName, array $variables = [], bool $ignoreUnknown = false)
    {
        $templatePaths = $this->baseRenderingContext->getTemplatePaths();
        $renderingContext = $this->baseRenderingContext;
        try {
            $parsedPartial = $renderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
                $templatePaths->getPartialIdentifier($partialName),
                function (RenderingContextInterface $renderingContext) use ($partialName): string {
                    return $renderingContext->getTemplatePaths()->getPartialSource($partialName);
                }
            );
            $parsedPartial->getArguments()->setRenderingContext($renderingContext);
            $this->renderingStack[] = $parsedPartial;
            if ($sectionName !== null) {
                $output = $this->renderSection($sectionName, $variables, $ignoreUnknown);
            } else {
                $parsedPartial->getArguments()->assignAll($variables)->validate();
                $output = $parsedPartial->evaluate($renderingContext);
            }
            array_pop($this->renderingStack);
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

        return $output;
    }

    protected function getCurrentParsedTemplate(): ComponentInterface
    {
        $renderingContext = $this->baseRenderingContext;
        $parsedTemplate = end($this->renderingStack);
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
            $this->baseTemplateClosure ?? function(RenderingContextInterface $renderingContext): string {
                return $renderingContext->getTemplatePaths()->getTemplateSource('Default', 'Default');
            }
        );
        return $parsedTemplate;
    }
}