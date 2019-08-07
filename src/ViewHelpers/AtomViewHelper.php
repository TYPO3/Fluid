<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Renders an Atom. Usually not used directly; instead, Atoms should
 * be registered as a namespace and will masquerace as this ViewHelper.
 */
class AtomViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('atom', 'mixed', 'Atom name or instance', true);
        $this->registerArgument('file', 'mixed', 'Atom file name, overrides "atom" if both are provided', true);
        $this->registerArgument('section', 'string', 'Optional name or dotted-name path to section to render from inside Atom');
        $this->registerArgument('optional', 'boolean', 'If Atom is not found and optional is true, does not throw exception error', false, false);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy();
        $optional = (boolean) ($arguments['optional'] ?? false);
        $default = $arguments['default'] ?? null;
        try {
            if (isset($arguments['file'])) {
                $component = $renderingContext->getTemplateParser()->parseFile($arguments['file']);
            } elseif (isset($arguments['atom'])) {
                $component = $arguments['atom'] instanceof ComponentInterface ? $arguments['atom'] : $renderingContext->getViewHelperResolver()->resolveAtom(...explode(':', $arguments['atom']));
            } else {
                $component = $this;
            }
        } catch (ChildNotFoundException $exception) {
            if ($optional) {
                return $default;
            }
            throw $exception;
        }
        if (!empty($arguments['section'])) {
            $component = $component->getNamedChild($arguments['section']);
        }
        $variables = (array) $arguments;
        $component->getArguments()->setRenderingContext($renderingContext)->assignAll($arguments);
        return $component->evaluate($renderingContext);
    }

    public function allowUndeclaredArgument(string $argumentName): bool
    {
        return true;
    }
}
