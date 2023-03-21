<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which handles object access. This means it handles structures like {object.accessor.bla}
 */
class ObjectAccessorNode extends AbstractNode
{

    /**
     * Object path which will be called. Is a list like "post.name.email"
     *
     * @var string
     */
    protected $objectPath;

    /**
     * Accessor names, one per segment in the object path.
     * Use constants from StandardVariableProvider.
     *
     * @var array
     */
    protected $accessors = [];

    /**
     * Constructor. Takes an object path as input.
     *
     * The first part of the object path has to be a variable in the
     * VariableProvider.
     *
     * @param string $objectPath An Object Path, like object1.object2.object3
     * @param array $accessors Optional list of accessor strategies; starting from beginning of dotted path. Incomplete allowed.
     */
    public function __construct($objectPath, array $accessors = [])
    {
        $this->objectPath = $objectPath;
        $this->accessors = $accessors;
    }

    /**
     * Internally used for building up cached templates; do not use directly!
     *
     * @return string
     */
    public function getObjectPath()
    {
        return $this->objectPath;
    }

    /**
     * @return array
     */
    public function getAccessors()
    {
        return $this->accessors;
    }

    /**
     * Evaluate this node and return the correct object.
     *
     * Handles each part (denoted by .) in $this->objectPath in the following order:
     * - call appropriate getter
     * - call public property, if exists
     * - fail
     *
     * The first part of the object path has to be a variable in the
     * VariableProvider.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed The evaluated object, can be any object type.
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $objectPath = strtolower($this->objectPath);
        $variableProvider = $renderingContext->getVariableProvider();
        if ($objectPath === '_all') {
            return $variableProvider->getAll();
        }
        return $variableProvider->getByPath($this->objectPath, $this->accessors);
    }
}
