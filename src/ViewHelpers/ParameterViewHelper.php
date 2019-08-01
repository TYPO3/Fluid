<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\EmbeddedComponentInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Parameter Declaration ViewHelper
 *
 * Declares one parameter for a template file or section,
 * depending on where the ViewHelper is used. The declared
 * argument must then be present when the template file
 * or section is rendered, or a parsing error will be thrown.
 *
 * Note that this ViewHelper and f:argument differ in
 * functionality in very important ways:
 *
 * - f:argument allows you to pass "arguments" which is normally
 *   an array, as separate tags for an easier to use syntax.
 * - whereas f:parameter is used to declare such an argument
 *   much like using $this->registerArgument in a VieWHelper
 *   registers a supported argument.
 *
 * The former is used to PASS ARGUMENTS. The latter is used
 * to DECLARE ARGUMENTS THAT CAN/MUST BE PASSED.
 */
class ParameterViewHelper extends AbstractViewHelper implements EmbeddedComponentInterface
{
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'Name of the parameter', true);
        $this->registerArgument('type', 'string', 'Data type of the parameter, e.g. string/int/bool', true);
        $this->registerArgument('description', 'string', 'Brief description of parameter. For increased detail you can use this ViewHelper in tag mode and add f:description inside the tag');
        $this->registerArgument('required', 'bool', 'If TRUE, becomes required parameter that causes errors if not provided', false, false);
        $this->registerArgument('default', 'mixed', 'Default value of the parameter if not required and not passed');
    }
}
