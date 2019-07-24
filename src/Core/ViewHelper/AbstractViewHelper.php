<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use Closure;
use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * The abstract base class for all view helpers.
 *
 * @api
 */
abstract class AbstractViewHelper extends AbstractComponent
{
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * @var Closure
     */
    protected $renderChildrenClosure = null;

    /**
     * Execute via Component API implementation.
     *
     * @param RenderingContextInterface $renderingContext
     * @param ArgumentCollection|null $arguments
     * @return mixed
     * @api
     */
    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollection $arguments = null)
    {
        $this->renderingContext = $arguments->getRenderingContext() ?? $renderingContext;
        return $this->callRenderMethod();
    }

    public function getArguments(): ArgumentCollection
    {
        if ($this->arguments === null) {
            $this->arguments = new ArgumentCollection();
            $this->initializeArguments();
        }
        return $this->arguments;
    }

    /**
     * Register a new argument. Call this method from your ViewHelper subclass
     * inside the initializeArguments() method.
     *
     * @param string $name Name of the argument
     * @param string $type Type of the argument
     * @param string $description Description of the argument
     * @param boolean $required If TRUE, argument is required. Defaults to FALSE.
     * @param mixed $defaultValue Default value of argument
     * @return AbstractViewHelper $this, to allow chaining.
     * @throws Exception
     * @api
     */
    protected function registerArgument(string $name, string $type, string $description, bool $required = false, $defaultValue = null): self
    {
        $this->getArguments()->addDefinition(new ArgumentDefinition($name, $type, $description, $required, $defaultValue));
        return $this;
    }

    /**
     * Overrides a registered argument. Call this method from your ViewHelper subclass
     * inside the initializeArguments() method if you want to override a previously registered argument.
     * @see registerArgument()
     *
     * @param string $name Name of the argument
     * @param string $type Type of the argument
     * @param string $description Description of the argument
     * @param boolean $required If TRUE, argument is required. Defaults to FALSE.
     * @param mixed $defaultValue Default value of argument
     * @return AbstractViewHelper $this, to allow chaining.
     * @throws Exception
     * @api
     */
    protected function overrideArgument(string $name, string $type, string $description, bool $required = false, $defaultValue = null): self
    {
        $this->getArguments()->addDefinition(new ArgumentDefinition($name, $type, $description, $required, $defaultValue));
        return $this;
    }

    /**
     * Called when being inside a cached template.
     *
     * @param Closure $renderChildrenClosure
     * @return void
     */
    public function setRenderChildrenClosure(Closure $renderChildrenClosure)
    {
        $this->renderChildrenClosure = $renderChildrenClosure;
    }

    /**
     * Call the render() method and handle errors.
     *
     * @return mixed the rendered ViewHelper
     * @throws Exception
     */
    protected function callRenderMethod()
    {
        if (method_exists($this, 'render')) {
            return call_user_func([$this, 'render']);
        }
        if (method_exists($this, 'renderStatic')) {
            // Method is safe to call - will not recurse through ViewHelperInvoker via the default
            // implementation of renderStatic() on this class.
            return call_user_func_array([static::class, 'renderStatic'], [$this->arguments->getArrayCopy(), $this->buildRenderChildrenClosure(), $this->arguments->getRenderingContext()]);
        }
        return $this->renderChildren();
    }

    /**
     * Helper method which triggers the rendering of everything between the
     * opening and the closing tag.
     *
     * @return mixed The finally rendered child nodes.
     * @api
     */
    protected function renderChildren()
    {
        if ($this->renderChildrenClosure !== null) {
            $closure = $this->renderChildrenClosure;
            return $closure();
        }
        return $this->evaluateChildren($this->arguments->getRenderingContext());
    }

    /**
     * Helper which is mostly needed when calling renderStatic() from within
     * render().
     *
     * No public API yet.
     *
     * @return Closure
     */
    protected function buildRenderChildrenClosure()
    {
        $self = clone $this;
        $renderChildrenClosure = function () use ($self) {
            return $self->renderChildren();
        };
        return $renderChildrenClosure;
    }

    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @return void
     * @api
     */
    protected function initializeArguments()
    {
    }

    public function allowUndeclaredArgument(string $argumentName): bool
    {
        return false;
    }

    /**
     * Tests if the given $argumentName is set, and not NULL.
     * The isset() test used fills both those requirements.
     *
     * @param string $argumentName
     * @return boolean TRUE if $argumentName is found, FALSE otherwise
     * @api
     */
    protected function hasArgument(string $argumentName): bool
    {
        return $this->getArguments()->offsetExists($argumentName);
    }
}
