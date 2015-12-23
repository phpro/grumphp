<?php

namespace GrumPHP\Linter;

/**
 * Class LintError
 *
 * @package GrumPHP\Linter
 */
class LintError
{
    const TYPE_NONE = 'none';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_FATAL = 'fatal';

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $column;

    /**
     * LintError constructor.
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
        $this->type = $type;
        $this->code = $code;
        $this->error = $error;
        $this->file = $file;
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
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
