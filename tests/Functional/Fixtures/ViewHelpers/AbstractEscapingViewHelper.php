<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AbstractEscapingViewHelper
 */
abstract class AbstractEscapingViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('content', 'string', 'Content provided as argument');
    }

    /**
     * @return string
     */
    public function render()
    {
        if (!isset($this->arguments['content'])) {
            $content = $this->renderChildren();
        } else {
            $content = $this->arguments['content'];
        }
        return $content;
    }
}
