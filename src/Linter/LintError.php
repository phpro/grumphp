<?php declare(strict_types=1);

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
     */
    public function __construct(string $typestring ,string  int $error, $file, $line)
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
