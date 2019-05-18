<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\NewParser;

use TYPO3Fluid\Fluid\Core\Parser\Context;

class Debugger
{
    private $fp;

    public function __construct(string $logFile)
    {
        $this->fp = fopen($logFile, 'w+');
    }

    public function __destruct()
    {
        fwrite($this->fp, PHP_EOL . PHP_EOL);
        fclose($this->fp);
    }

    public function writeLogContent(string $content): void
    {
        fwrite($this->fp, $content);
    }

    public function writeLogLine(string $line, int $color = 0): void
    {
        $this->writeLogContent(($color === 0 ? $line : "\033[01;" . (string) $color . 'm' . $line . "\033[01;0m") . PHP_EOL);
    }

    public function debugSequence(\Iterator $sequence): void
    {
        $colors = [
            "\033[01;0m",
            "\033[01;31m",
            "\033[01;32m",
            "\033[01;34m",
            "\033[01;35m",
            "\033[01;36m",
        ];

        $reflect = new \ReflectionClass(Context::class);
        $constants = array_flip($reflect->getConstants());
        $legend = '';
        foreach ($constants as $index => $constant) {
            $legend .= $colors[$index] ?? "\033[01;0m";
            $legend .= substr($constant, 8);
            $legend .= "\033[0m";
            $legend .= ' ';
        }

        $captures = [];
        $symbols = [];
        $contexts = [];
        $nesting = [];

        $spacing = '';

        try {
            foreach ($sequence as $symbol => $capture) {
                $captures[] = $capture->captured === null ? null : str_replace(PHP_EOL, '', $capture->captured);
                $symbols[] = $symbol;
                $contexts[] = $sequencer->position->context->context;
                $nesting[] = count($sequencer->position->stack);
            }
        } catch (\RuntimeException $exception) {
            $this->writeLogLine($exception->getMessage(), 31);
        }


        $this->writeLogLine(PHP_EOL);
        $this->writeLogLine($legend);
        $this->writeLogLine(str_repeat('—', strlen(str_replace(PHP_EOL, '', $sequencer->source->source))));

        $symbolLine = '';
        foreach ($symbols as $index => $symbol) {
            $char = $symbol === Splitter::BYTE_BACKSLASH ? '\\' : chr($symbol);
            $capturedLength = strlen((string)$captures[$index]) + 1;
            $symbolLine .= $colors[$contexts[$index]] ?? "\033[01;0m";
            $symbolLine .= str_repeat(' ', max($capturedLength - 1, 0)) . $char . $spacing;
            #echo str_repeat((string)$contexts[$index], $capturedLength > 0 ? $capturedLength : 1);
            $symbolLine .= "\033[0m";
        }

        $this->writeLogLine($symbolLine, 0);

        $captureLine = '';
        foreach ($captures as $index => $capture) {
            $char = $symbol === Splitter::BYTE_BACKSLASH ? '\\' : chr($symbol);
            $captureLine .= $colors[$contexts[$index]] ?? "\033[01;0m";
            $captureLine .= $capture;
            //$captureLine .= str_pad(addslashes(chr($symbols[$index])), 1, ' ') . '   ';
            $captureLine .= str_repeat(' ', strlen($char)) . $spacing;
            $captureLine .= "\033[0m";
        }

        $this->writeLogLine($captureLine);

        $nestingLine = '';
        foreach ($nesting as $index => $depth) {
            $capturedLength = strlen(str_replace(PHP_EOL, '', (string)$captures[$index])) + 1;
            $nestingLine .= str_repeat((string)$depth, ($capturedLength > 0 ? $capturedLength : 0)) . $spacing;
        }

        $this->writeLogLine($nestingLine);
        $this->writeLogLine(str_repeat('—', strlen(str_replace(PHP_EOL, '', $sequencer->source->source))));
    }
}