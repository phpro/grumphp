<?php declare(strict_types=1);

namespace GrumPHP\Linter\Json;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use GrumPHP\Util\Filesystem;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use SplFileInfo;

class JsonLinter implements LinterInterface
{
    /**
     * @var bool
     */
    private $detectKeyConflicts = false;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var JsonParser
     */
    private $jsonParser;

    /**
     * JsonLinter constructor.
     *
     */
    public function __construct(Filesystem $filesystem, JsonParser $jsonParser)
    {
        $this->filesystem = $filesystem;
        $this->jsonParser = $jsonParser;
    }

    /**
     *
     * @throws \Seld\JsonLint\ParsingException
     */
    public function lint(SplFileInfo $file): mixed
    {
        $errors = new LintErrorsCollection();
        $flags = $this->calculateFlags();

        try {
            $json = $this->filesystem->readFromFileInfo($file);
            $this->jsonParser->parse($json, $flags);
        } catch (ParsingException $exception) {
            $errors->add(JsonLintError::fromParsingException($file, $exception));
        }

        return $errors;
    }

    
    public function isInstalled(): bool
    {
        return class_exists(JsonParser::class);
    }

    
    public function setDetectKeyConflicts(boolean $detectKeyConflicts)
    {
        $this->detectKeyConflicts = $detectKeyConflicts;
    }

    
    private function calculateFlags(): int
    {
        $flags = 0;
        $flags += $this->detectKeyConflicts ? JsonParser::DETECT_KEY_CONFLICTS : 0;

        return $flags;
    }
}
