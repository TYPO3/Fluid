<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;

/**
 * This ViewHelper prevents rendering of any content inside the tag.
 *
 * Contents of the comment will still be **parsed** thus throwing an
 * Exception if it contains syntax errors. You can put child nodes in
 * CDATA tags to avoid this.
 *
 * Using this ViewHelper won't have a notable effect on performance,
 * especially once the template is parsed.  However it can lead to reduced
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
 * Prevent parsing
 * ---------------
 *
 * ::
 *
 *     <f:comment><![CDATA[
 *        <f:some.invalid.syntax />
 *     ]]></f:comment>
 *
 * Output:
 *
 * Will be nothing.
 *
 * @api
 */
class CommentViewHelper extends AbstractViewHelper
{
    use ParserRuntimeOnly;

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;
}
