<?php

declare(strict_types=1);

namespace GrumPHP\Linter\Yaml;

use GrumPHP\Linter\LintError;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlLintError extends LintError
{
    private $snippet;

    public function __construct(string $type, string $error, string $file, int $line = -1, string $snippet = '')
    {
        parent::__construct($type, $error, $file, $line);
        $this->snippet = $snippet;
    }

    public static function fromParseException(ParseException $exception): self
    {
        return new self(
            LintError::TYPE_ERROR,
            $exception->getMessage(),
            $exception->getParsedFile(),
            $exception->getParsedLine(),
            $exception->getSnippet()
        );
    }

    public function getSnippet(): string
    {
        return $this->snippet;
    }

    public function __toString(): string
    {
        return sprintf('[%s] %s', strtoupper($this->getType()), $this->getError());
    }
}
