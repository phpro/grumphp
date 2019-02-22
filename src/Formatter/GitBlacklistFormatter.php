<?php

declare(strict_types=1);

namespace GrumPHP\Formatter;

use GrumPHP\IO\IOInterface;
use Symfony\Component\Process\Process;

class GitBlacklistFormatter implements ProcessFormatterInterface
{
    const WORD_COLOR = "\033[1;31";
    const RESET_COLOR = "\033[m";
    const COLON_COLOR = "\033[36m";
    const SPACE_BEFORE = 20;
    const SPACE_AFTER = 20;

    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * GitBlacklistFormatter constructor.
     */
    public function __construct(IOInterface $IO)
    {
        $this->IO = $IO;
    }

    public function format(Process $process): string
    {
        $output = $process->getOutput();
        if (!$output) {
            return $process->getErrorOutput();
        }
        if (!$this->IO->isDecorated()) {
            return $output;
        }

        return $this->formatOutput($output);
    }

    private function formatOutput(string $output): string
    {
        $result = static::RESET_COLOR;
        foreach (array_filter(explode("\n", $output)) as $lineNumber => $line) {
            $result .= preg_match('/^[0-9]+/', $line) ? $this->trimOutputLine($line, (int) $lineNumber) : $line;
            $result .= PHP_EOL;
        }

        return trim($result);
    }

    private function trimOutputLine(string $line, int $lineNumber): string
    {
        if (\strlen($line) < 80) {
            return $line;
        }

        $positionsWordColor = [];
        $positionsResetColor = [];
        $parts = [];
        $lastPos = 0;

        //iterate over all WORD_COLORs and save the positions into $positionsWordColor
        while (false !== ($lastPos = mb_strpos($line, static::WORD_COLOR, $lastPos))) {
            $positionsWordColor[] = $lastPos;
            $lastPos += mb_strlen(static::WORD_COLOR);
        }
        $lastPos = 0;

        //iterate over all RESET_COLORs and save the positions into $positionsResetColor
        while (false !== ($lastPos = mb_strpos($line, static::RESET_COLOR, $lastPos))) {
            $positionsResetColor[] = $lastPos;
            $lastPos += mb_strlen(static::RESET_COLOR);
        }

        foreach ($positionsWordColor as $pos) {
            do {
                $pos2 = array_shift($positionsResetColor);
            } while ($pos2 < $pos);

            $pos -= static::SPACE_BEFORE;
            //$pos can't be lower then 0 else we start at the end of the string instead of the beginning
            if ($pos < 0) {
                $pos = 0;
            }
            $pos2 += static::SPACE_AFTER;

            $part = '  '.$lineNumber.static::COLON_COLOR.':'.static::RESET_COLOR;
            $part .= ($pos + static::SPACE_BEFORE).static::COLON_COLOR.':'.static::RESET_COLOR;
            $part .= ' '.mb_substr($line, $pos, $pos2 - $pos).static::RESET_COLOR;
            $parts[] = $part;
        }

        return implode(PHP_EOL, $parts);
    }
}
