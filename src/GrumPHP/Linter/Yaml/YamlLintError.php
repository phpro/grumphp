<?php

namespace GrumPHP\Linter\Yaml;

use GrumPHP\Linter\LintError;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlLintError extends LintError
{
    /**
     * @var string
     */
    private $snippet;

    /**
     * YamlLintError constructor.
     *
     * @param string $type
     * @param string $error
     * @param string $file
     * @param int    $line
     * @param string $snippet
     */
    public function __construct($type, $error, $file, $line = -1, $snippet = null)
    {
        parent::__construct($type, $error, $file, $line);
        $this->snippet = $snippet;
    }

    /**
     * @param ParseException $exception
     *
     * @return YamlLintError
     */
    public static function fromParseException(ParseException $exception)
    {
        return new YamlLintError(
            LintError::TYPE_ERROR,
            $exception->getMessage(),
            $exception->getParsedFile(),
            $exception->getParsedLine(),
            $exception->getSnippet()
        );
    }

    /**
     * @return string
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('[%s] %s', strtoupper($this->getType()), $this->getError());
    }
}
