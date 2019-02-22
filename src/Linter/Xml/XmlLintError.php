<?php

declare(strict_types=1);

namespace GrumPHP\Linter\Xml;

use GrumPHP\Linter\LintError;
use LibXMLError;

class XmlLintError extends LintError
{
    private $code;
    private $column;

    public function __construct(
        string $type,
        int $code,
        string $error,
        string $file,
        int $line,
        int $column
    ) {
        parent::__construct($type, $error, $file, $line);
        $this->code = $code;
        $this->column = $column;
    }

    public static function fromLibXmlError(LibXMLError $error): self
    {
        $type = LintError::TYPE_NONE;
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $type = LintError::TYPE_WARNING;
                break;
            case LIBXML_ERR_FATAL:
                $type = LintError::TYPE_FATAL;
                break;
            case LIBXML_ERR_ERROR:
                $type = LintError::TYPE_ERROR;
                break;
        }

        return new self($type, $error->code, $error->message, $error->file, $error->line, $error->column);
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s: %s (%s) on line %s,%s',
            strtoupper($this->getType()),
            $this->getFile(),
            $this->getError(),
            $this->getCode() ?: 0,
            $this->getLine(),
            $this->getColumn() ?: 0
        );
    }
}
