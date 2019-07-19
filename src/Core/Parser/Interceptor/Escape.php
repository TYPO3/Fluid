<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\Interceptor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;

/**
 * An interceptor adding the "Htmlspecialchars" viewhelper to the suitable places.
 */
class Escape implements InterceptorInterface
{
    /**
     * A counter of ViewHelperNodes which currently disable the interceptor.
     * Needed to enable the interceptor again.
     *
     * @var int
     */
    protected $viewHelperNodesWhichDisableTheInterceptor = 0;

    /**
     * Adds a ViewHelper node using the Format\HtmlspecialcharsViewHelper to the given node.
     * If "escapingInterceptorEnabled" in the ViewHelper is FALSE, will disable itself inside the ViewHelpers body.
     *
     * @param ComponentInterface $node
     * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
     * @param ParsingState $parsingState the current parsing state. Not needed in this interceptor.
     * @return ComponentInterface
     */
    public function process(ComponentInterface $node, int $interceptorPosition, ParsingState $parsingState): ComponentInterface
    {
        if ($interceptorPosition === InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER) {
            if (!$node->isChildrenEscapingEnabled()) {
                ++$this->viewHelperNodesWhichDisableTheInterceptor;
            }
        } elseif ($interceptorPosition === InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER) {
            if (!$node->isChildrenEscapingEnabled()) {
                --$this->viewHelperNodesWhichDisableTheInterceptor;
            }

            if ($this->viewHelperNodesWhichDisableTheInterceptor === 0 && $node->isOutputEscapingEnabled()) {
                $node = new EscapingNode($node);
            }
        } elseif ($interceptorPosition === InterceptorInterface::INTERCEPT_SELFCLOSING_VIEWHELPER) {
            if ($this->viewHelperNodesWhichDisableTheInterceptor === 0 && $node->isOutputEscapingEnabled()) {
                $node = new EscapingNode($node);
            }
        } elseif ($this->viewHelperNodesWhichDisableTheInterceptor === 0 && ($node instanceof ObjectAccessorNode || $node instanceof ExpressionNodeInterface)) {
            $node = new EscapingNode($node);
        }
        return $node;
    }

    /**
     * This interceptor wants to hook into object accessor creation, and opening / closing ViewHelpers.
     *
     * @return array Array of INTERCEPT_* constants
     */
    public function getInterceptionPoints(): array
    {
        return [
            InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER,
            InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
            InterceptorInterface::INTERCEPT_SELFCLOSING_VIEWHELPER,
            InterceptorInterface::INTERCEPT_OBJECTACCESSOR,
            InterceptorInterface::INTERCEPT_EXPRESSION,
        ];
    }
}
