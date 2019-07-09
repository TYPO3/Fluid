<?php
declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

/**
 * Position class, used by Sequencer when reporting template
 * sequencing errors. Indicates a position in a Source.
 */
class Position
{
    // Crazy enough, the output of "unpack()" is indexed starting from 1, not 0.
    public $index = 1;

    /** @var string|null */
    public $captured = null;

    /** @var Context */
    public $context;

    public function __construct(Context $context, int $index = 1, ?string $captured = null)
    {
        $this->context = $context;
        $this->index = $index;
        $this->captured = $captured;
    }
}
