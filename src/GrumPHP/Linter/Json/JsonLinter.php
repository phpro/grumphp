<?php

namespace GrumPHP\Linter\Json;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use SplFileInfo;

/**
 * Class JsonLinter
 *
 * @package GrumPHP\Linter\Json
 */
class JsonLinter implements LinterInterface
{
    /**
     * @var bool
     */
    private $detectKeyConflicts = false;

    /**
     * @param SplFileInfo $file
     *
     * @return mixed
     * @throws \Seld\JsonLint\ParsingException
     */
    public function lint(SplFileInfo $file)
    {
        $parser = new JsonParser();
        $errors = new LintErrorsCollection();
        $flags = $this->calculateFlags();

        try {
            $json = file_get_contents($file->getPathname());
            $parser->parse($json, $flags);
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
        return class_exists('Seld\JsonLint\JsonParser');
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
