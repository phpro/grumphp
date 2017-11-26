<?php declare(strict_types=1);

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
    public function __construct(string $type, string $error, string $file, int $line = -1, string $snippet = null)
    {
        parent::__construct($type, $error, $file, $line);
        $this->snippet = $snippet;
    }

    /**
     *
     * @return YamlLintError
     */
    public static function fromParseException(ParseException $exception): YamlLintError
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
    public function getSnippet(): string
    {
        return $this->snippet;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('[%s] %s', strtoupper($this->getType()), $this->getError());
    }
}
