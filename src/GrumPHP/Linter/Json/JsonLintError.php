<?php

namespace GrumPHP\Linter\Json;

use GrumPHP\Linter\LintError;
use Seld\JsonLint\ParsingException;
use SplFileInfo;

class JsonLintError extends LintError
{
    /**
     * @param SplFileInfo      $file
     * @param ParsingException $exception
     *
     * @return JsonLintError
     */
    public static function fromParsingException(SplFileInfo $file, ParsingException $exception)
    {
        return new JsonLintError(LintError::TYPE_ERROR, $exception->getMessage(), $file->getPathname(), 0);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '[%s] %s: %s',
            strtoupper($this->getType()),
            $this->getFile(),
            $this->getError()
        );
    }
}
