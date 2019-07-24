<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;

/**
 * This interface is returned by \TYPO3Fluid\Fluid\Core\Parser\TemplateParser->parse()
 * method and is a parsed template
 *
 * @deprecated To be removed in Fluid 4.0
 */
interface ParsedTemplateInterface extends ComponentInterface
{
}
