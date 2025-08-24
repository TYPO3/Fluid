<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper prevents rendering of any content inside the tag.
 *
 * Contents of the comment will **not be parsed** thus it can be used to
 * comment out invalid Fluid syntax or non-existent ViewHelpers during
 * development.
 *
 * Using this ViewHelper won't have a notable effect on performance,
 * especially once the template is parsed. However, it can lead to reduced
 * readability. You can use layouts and partials to split a large template
 * into smaller parts. Using self-descriptive names for the partials can
 * make comments redundant.
 *
 * Examples
 * ========
 *
 * Commenting out fluid code
 * -------------------------
 *
 * ::
 *
 *     Before
 *     <f:comment>
 *         This is completely hidden.
 *         <f:debug>This does not get rendered</f:debug>
 *     </f:comment>
 *     After
 *
 * Output::
 *
 *     Before
 *     After
 *
 * @api
 */
class CommentViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected ?bool $escapeChildren = false;

    /**
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function render(): string
    {
        return '';
    }

    /**
     * This VH does not ever output anything. We optimize compilation
     * to always return an empty string.
     */
    final public function convert(TemplateCompiler $templateCompiler): array
    {
        return [
            'initialization' => '',
            'execution' => '\'\'',
        ];
    }
}
