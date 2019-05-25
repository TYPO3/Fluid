<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\Parser;

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

    public function getContextName(): string
    {
        static $consts = [];
        if (empty($consts)) {
            $reflect = new \ReflectionClass(Context::class);
            $consts = array_flip($reflect->getConstants());
        }
        return $consts[$this->context->context] ?? 'UNKNOWN';
    }
}
