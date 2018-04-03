<?php

declare(strict_types=1);

namespace GrumPHP\Linter\Json;

use GrumPHP\Linter\LintError;
use Seld\JsonLint\ParsingException;
use SplFileInfo;

class JsonLintError extends LintError
{
    public static function fromParsingException(SplFileInfo $file, ParsingException $exception): self
    {
        return new self(LintError::TYPE_ERROR, $exception->getMessage(), $file->getPathname(), 0);
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s: %s',
            strtoupper($this->getType()),
            $this->getFile(),
            $this->getError()
        );
    }
}
