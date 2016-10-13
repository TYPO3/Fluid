<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * A key/value store that can be used by ViewHelpers to communicate between each other.
 *
 * @api
 */
class ViewHelperVariableContainer
{

    /**
     * Two-dimensional object array storing the values. The first dimension is the fully qualified ViewHelper name,
     * and the second dimension is the identifier for the data the ViewHelper wants to store.
     *
     * @var array
     */
    protected $objects = [];

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * Add a variable to the Variable Container. Make sure that $viewHelperName is ALWAYS set
     * to your fully qualified ViewHelper Class Name
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @param mixed $value The value to store
     * @return void
     * @api
     */
    public function add($viewHelperName, $key, $value)
    {
        $this->addOrUpdate($viewHelperName, $key, $value);
    }

    /**
     * Add a variable to the Variable Container. Make sure that $viewHelperName is ALWAYS set
     * to your fully qualified ViewHelper Class Name.
     * In case the value is already inside, it is silently overridden.
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @param mixed $value The value to store
     * @return void
     */
    public function addOrUpdate($viewHelperName, $key, $value)
    {
        if (!isset($this->objects[$viewHelperName])) {
            $this->objects[$viewHelperName] = [];
        }
        $this->objects[$viewHelperName][$key] = $value;
    }

    /**
     * Gets a variable which is stored
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @param mixed $default Default value to use if no value is found.
     * @return mixed The object stored
     * @api
     */
    public function get($viewHelperName, $key, $default = null)
    {
        if ($this->exists($viewHelperName, $key)) {
            return $this->objects[$viewHelperName][$key];
        }
        return $default;
    }

    /**
     * Determine whether there is a variable stored for the given key
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @return boolean TRUE if a value for the given ViewHelperName / Key is stored, FALSE otherwise.
     * @api
     */
    public function exists($viewHelperName, $key)
    {
        return isset($this->objects[$viewHelperName]) && array_key_exists($key, $this->objects[$viewHelperName]);
    }

    /**
     * Remove a value from the variable container
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data to remove
     * @return void
     * @api
     */
    public function remove($viewHelperName, $key)
    {
        if ($this->exists($viewHelperName, $key)) {
            unset($this->objects[$viewHelperName][$key]);
        }
    }

    /**
     * Set the view to pass it to ViewHelpers.
     *
     * @param ViewInterface $view View to set
     * @return void
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;
    }

    /**
     * Get the view.
     *
     * !!! This is NOT a public API and might still change!!!
     *
     * @return ViewInterface The View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Clean up for serializing.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['objects'];
    }
}
