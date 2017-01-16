<?php

namespace GrumPHP\Linter;

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
     * LintError constructor.
     *
     * @param string $type
     * @param string $error
     * @param string $file
     * @param int    $line
     */
    public function __construct($type, $error, $file, $line)
    {
        $this->type = $type;
        $this->error = $error;
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @return string
     */
    public function __toString()
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
