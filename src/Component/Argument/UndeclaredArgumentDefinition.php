<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class UndeclaredArgumentDefinition extends ArgumentDefinition
{
    /**
     * Name of argument
     *
     * @var string
     */
    protected $name = 'undeclared';

    /**
     * Type of argument
     *
     * @var string
     */
    protected $type = 'mixed';

    /**
     * Description of argument
     *
     * @var string
     */
    protected $description = 'An undeclared argument';

    /**
     * Is argument required?
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * Default value for argument
     *
     * @var mixed
     */
    protected $defaultValue = null;

    public function __construct($name, $type = 'mixed', $description = 'An undeclared argument', $required = false, $defaultValue = null)
    {
        parent::__construct($name, $type, $description, $required, $defaultValue);
    }
}
