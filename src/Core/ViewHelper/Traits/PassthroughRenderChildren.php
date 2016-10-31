<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/**
 * Class PassthroughRenderChildren
 */
trait PassthroughRenderChildren
{
    /**
     * @return mixed
     */
    abstract protected function renderChildren();

    /**
     * @return string the rendered string
     * @api
     */
    public function render()
    {
        return $this->renderChildren();
    }

}
