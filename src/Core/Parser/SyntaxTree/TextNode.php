<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Text Syntax Tree Node - is a container for strings.
 */
class TextNode extends AbstractComponent
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
     * @throws Exception
     */
    public function __construct(string $text)
    {
        if (!is_string($text)) {
            throw new Exception('Text node requires an argument of type string, "' . gettype($text) . '" given.');
        }
        $this->text = $text;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $this->text;
    }

    public function flatten(bool $extractNode = false)
    {
        if ($extractNode) {
            return $this->text;
        }
        return $this;
    }

    /**
     * Getter for text
     *
     * @return string The text of this node
     */
    public function getText(): string
    {
        return $this->text;
    }

    public function appendText(string $text): self
    {
        $this->text .= $text;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getText();
    }
}
