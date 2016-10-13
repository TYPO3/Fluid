<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\ChainedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to insert variables which only apply during
 * cache warmup and only apply if no other variables are
 * specified for the warmup process.
 *
 * If a chunk of template code is impossible to compile
 * without additional variables, for example when rendering
 * sections or partials using dynamic names, you can use this
 * ViewHelper around that chunk and specify a set of variables
 * which will be assigned only while compiling the template
 * and only when this is done as part of cache warmup. The
 * template chunk can then be compiled using those default
 * variables.
 *
 * Note: this does not imply that only those variable values
 * will be used by the compiled template. It only means that
 * DEFAULT values of vital variables will be present during
 * compiling.
 *
 * If you find yourself completely unable to properly warm up
 * a specific template file even with use of this ViewHelper,
 * then you can consider using `f:cache.disable` to prevent
 * the template compiler from even attempting to compile it.
 *
 * USE WITH CARE! SOME EDGE CASES OF FOR EXAMPLE VIEWHELPERS
 * WHICH REQUIRE SPECIAL VARIABLE TYPES MAY NOT BE SUPPORTED
 * HERE DUE TO THE RUDIMENTARY NATURE OF VARIABLES YOU DEFINE.
 *
 * = Examples =
 *
 * <code title="Usage and effect">
 * <f:cache.warmup variables="{foo: bar}">
 *    Template code depending on {foo} variable which is not
 *    assigned when warming up Fluid's caches. {foo} is only
 *    assigned if the variable does not already exist and the
 *    assignment only happens if Fluid is in warmup mode.
 * </f:cache.warmup>
 * </code>
 *
 * @api
 */
class WarmupViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'variables',
            'array',
            'Array of variables to assign ONLY when compiling. See main class documentation.',
            false,
            []
        );
    }

    /**
     * Render this ViewHelper
     *
     * Makes a decision based on whether or not warmup mode is
     * currently active - if it is NOT ACTIVE the ViewHelper
     * returns the result of `renderChildren` without any further
     * operations. If ACTIVE the ViewHelper will assign/overlay
     * replacement variables, call `renderChildren`, restore the
     * original variable provider and finally return the content.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->renderingContext->getTemplateCompiler()->isWarmupMode()) {
            return $this->renderChildren();
        }
        $originalVariableProvider = static::overlayVariablesIfNotSet(
            $this->renderingContext,
            $this->arguments['variables']
        );
        $content = $this->renderChildren();
        $this->renderingContext->setVariableProvider($originalVariableProvider);
        return $content;
    }

    /**
     * Custom implementation of compile method. Performns variable
     * provider overlaying, calls renderChildren and throws a
     * StopCompilingChildren with a static replacement string attached.
     *
     * TemplateCompiler then inserts this string as a static string in
     * the compiled template (and stops compiling all child nodes).
     *
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     */
    public function compile(
        $argumentsName,
        $closureName,
        &$initializationPhpCode,
        ViewHelperNode $node,
        TemplateCompiler $compiler
    ) {
        $originalVariableProvider = static::overlayVariablesIfNotSet($this->renderingContext, $this->arguments);
        $stopCompilingChildrenException = new StopCompilingChildrenException();
        $stopCompilingChildrenException->setReplacementString($this->renderChildren());
        $this->renderingContext->setVariableProvider($originalVariableProvider);
        throw $stopCompilingChildrenException;
    }

    /**
     * Overlay variables by replacing the VariableProvider with a
     * ChainedVariableProvider using dual data sources. Returns the
     * original VariableProvider which must replace the temporary
     * one again once the rendering/compiling is done.
     *
     * @param RenderingContextInterface $renderingContext
     * @param array $variables
     * @return VariableProviderInterface
     */
    protected static function overlayVariablesIfNotSet(RenderingContextInterface $renderingContext, array $variables)
    {
        $currentProvider = $renderingContext->getVariableProvider();
        $chainedVariableProvider = new ChainedVariableProvider([
            $currentProvider,
            new StandardVariableProvider($variables)
        ]);
        $renderingContext->setVariableProvider($chainedVariableProvider);
        return $currentProvider;
    }
}
