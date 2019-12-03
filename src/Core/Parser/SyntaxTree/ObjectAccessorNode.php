<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which handles object access. This means it handles structures like {object.accessor.bla}
 */
class ObjectAccessorNode extends AbstractComponent
{
    protected $escapeChildren = false;

    /**
     * Constructor. Takes an object path or null as input;
     * if null is provided, the object path is determined by
     * evaluating the child nodes.
     *
     * The first part of the object path has to be a variable in the
     * VariableProvider.
     *
     * @param string|null $objectPath An Object Path, like object1.object2.object3. If NULL you must provide child nodes.
     */
    public function __construct(?string $objectPath = null)
    {
        if ($objectPath !== null) {
            $this->addChild(new TextNode($objectPath));
        }
    }

    public function flatten(bool $extractNode = false)
    {
        return $this;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $objectPath = (string) $this->evaluateChildNodes($renderingContext);
        $variableProvider = $renderingContext->getVariableProvider();
        if ($objectPath === '_all') {
            return $variableProvider->getAll();
        }
        return $variableProvider->getByPath($objectPath);
    }
}
