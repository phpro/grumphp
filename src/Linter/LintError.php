<?php

declare(strict_types=1);

namespace GrumPHP\Linter;

class LintError
{
    const TYPE_NONE = 'none';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_FATAL = 'fatal';

    private $type;
    private $error;
    private $file;
    private $line;

    public function __construct(string $type, string $error, string $file, int $line)
    {
        $this->type = $type;
        $this->error = $error;
        $this->file = $file;
        $this->line = $line;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s: %s on line %s',
            strtoupper($this->getType()),
            $this->getFile(),
            $this->getError(),
            $this->getLine()
        );
    }
}
