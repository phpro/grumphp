<?php

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
     * @param Filesystem $filesystem
     * @param JsonParser $jsonParser
     */
    public function __construct(Filesystem $filesystem, JsonParser $jsonParser)
    {
        $this->filesystem = $filesystem;
        $this->jsonParser = $jsonParser;
    }

    /**
     * @param SplFileInfo $file
     *
     * @return mixed
     * @throws \Seld\JsonLint\ParsingException
     */
    public function lint(SplFileInfo $file)
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

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return class_exists(JsonParser::class);
    }

    /**
     * @param boolean $detectKeyConflicts
     */
    public function setDetectKeyConflicts($detectKeyConflicts)
    {
        $this->detectKeyConflicts = $detectKeyConflicts;
    }

    /**
     * @return int
     */
    private function calculateFlags()
    {
        $flags = 0;
        $flags += $this->detectKeyConflicts ? JsonParser::DETECT_KEY_CONFLICTS : 0;

        return $flags;
    }
}
