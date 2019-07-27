<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which handles object access. This means it handles structures like {object.accessor.bla}
 */
class ObjectAccessorNode extends AbstractComponent
{

    /**
     * Object path which will be called. Is a list like "post.name.email"
     *
     * @var string
     */
    protected $objectPath;

    /**
     * Accessor names, one per segment in the object path. Use constants from VariableExtractor
     *
     * @var array
     */
    protected $accessors = [];

    /**
     * Constructor. Takes an object path or null as input;
     * if null is provided, the object path is determined by
     * evaluating the child nodes - or the single child node
     * is rendered directly if it is a ViewHelperNode.
     *
     * The first part of the object path has to be a variable in the
     * VariableProvider.
     *
     * @param string|null $objectPath An Object Path, like object1.object2.object3. If NULL, child nodes will be evaluated to generate a property path
     *                                UNLESS the only child of the node is a ViewHelper, in which case, that ViewHelper is evaluated
     * @param array $accessors Optional list of accessor strategies; starting from beginning of dotted path. Incomplete allowed.
     */
    public function __construct(?string $objectPath, array $accessors = [])
    {
        $this->objectPath = $objectPath;
        $this->accessors = $accessors;
    }

    /**
     * @param string|null $objectPath
     */
    public function setObjectPath(?string $objectPath): void
    {
        $this->objectPath = $objectPath;
    }

    /**
     * Internally used for building up cached templates; do not use directly!
     *
     * @return string|null
     */
    public function getObjectPath(): string
    {
        return $this->objectPath;
    }

    /**
     * @return array
     */
    public function getAccessors(): iterable
    {
        return $this->accessors;
    }

    public function flatten(bool $extractNode = false)
    {
        return $this;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $children = $this->getChildren();
        $numberOfChildNodes = count($children);
        if ($this->objectPath !== null && $numberOfChildNodes > 0) {
            throw new Exception('An ObjectAccessor can use either a string variable path or child nodes - but not both', 1559241805);
        }
        $objectPath = strtolower($this->objectPath ?? $this->evaluateChildren($renderingContext));
        $variableProvider = $renderingContext->getVariableProvider();
        if ($objectPath === '_all') {
            return $variableProvider->getAll();
        }
        return $variableProvider->getByPath($this->objectPath, $this->accessors);
    }
}
