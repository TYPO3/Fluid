<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class ParserRuntimeOnly
 */
trait ParserRuntimeOnly
{
    /**
     * @return null
     */
    public function render()
    {
        return null;
    }
}