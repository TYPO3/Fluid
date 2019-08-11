<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;

/**
 * Root node of every syntax tree.
 */
class RootNode extends AbstractComponent
{
    protected $quoted = false;

    protected $escapeOutput = false;

    public function isQuoted(): bool
    {
        return $this->quoted;
    }

    public function setQuoted(bool $quoted): self
    {
        $this->quoted = $quoted;
        return $this;
    }
}
