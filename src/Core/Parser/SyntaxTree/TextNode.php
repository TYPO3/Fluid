<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Text Syntax Tree Node - is a container for strings.
 */
class TextNode extends AbstractNode
{

    /**
     * Contents of the text node
     *
     * @var string
     */
    protected $text;

    /**
     * Constructor.
     *
     * @param string $text text to store in this textNode
     * @throws Parser\Exception
     */
    public function __construct($text)
    {
        if (!is_string($text)) {
            throw new Parser\Exception('Text node requires an argument of type string, "' . gettype($text) . '" given.');
        }
        $this->text = $text;
    }

    /**
     * Return the text associated to the syntax tree. Text from child nodes is
     * appended to the text in the node's own text.
     *
     * @param RenderingContextInterface $renderingContext
     * @return string the text stored in this node/subtree.
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $this->text;
    }

    /**
     * Getter for text
     *
     * @return string The text of this node
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getText();
    }
}
