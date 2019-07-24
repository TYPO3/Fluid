<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;

/**
 * An interceptor interface. Interceptors are used in the parsing stage to change
 * the syntax tree of a template, e.g. by adding viewhelper nodes.
 */
interface InterceptorInterface
{

    const INTERCEPT_OPENING_VIEWHELPER = 1;
    const INTERCEPT_CLOSING_VIEWHELPER = 2;
    const INTERCEPT_TEXT = 3;
    const INTERCEPT_OBJECTACCESSOR = 4;
    const INTERCEPT_EXPRESSION = 5;
    const INTERCEPT_SELFCLOSING_VIEWHELPER = 6;

    /**
     * The interceptor can process the given node at will and must return a node
     * that will be used in place of the given node.
     *
     * @param ComponentInterface $node
     * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
     * @return ComponentInterface
     */
    public function process(ComponentInterface $node, int $interceptorPosition): ComponentInterface;

    /**
     * The interceptor should define at which interception positions it wants to be called.
     *
     * @return array Array of INTERCEPT_* constants
     */
    public function getInterceptionPoints(): array;
}
