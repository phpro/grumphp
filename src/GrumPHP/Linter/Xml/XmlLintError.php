<?php

namespace GrumPHP\Linter\Xml;

use GrumPHP\Linter\LintError;
use LibXMLError;

class XmlLintError extends LintError
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var int
     */
    private $column;

    /**
     * XmlLintError constructor.
     *
     * @param string $type
     * @param int    $code
     * @param string $error
     * @param string $file
     * @param int    $line
     * @param int    $column
     */
    public function __construct($type, $code, $error, $file, $line, $column)
    {
        parent::__construct($type, $error, $file, $line);
        $this->code = $code;
        $this->column = $column;
    }

    /**
     * @param LibXMLError $error
     *
     * @return XmlLintError
     */
    public static function fromLibXmlError(LibXMLError $error)
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

        return new XmlLintError($type, $error->code, $error->message, $error->file, $error->line, $error->column);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function __toString()
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
