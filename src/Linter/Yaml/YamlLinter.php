<?php

declare(strict_types=1);

namespace GrumPHP\Linter\Yaml;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use GrumPHP\Util\Filesystem;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlLinter implements LinterInterface
{
    /**
     * True if object support is enabled, false otherwise.
     *
     * @var bool
     */
    private $objectSupport = false;

    /**
     * True if an exception must be thrown on invalid types false otherwise.
     *
     * @var bool
     */
    private $exceptionOnInvalidType = false;

    /**
     * True if custom tags needs to be parsed.
     *
     * @var bool
     */
    private $parseCustomTags = false;

    /**
     * True if PHP constants needs to be parsed.
     *
     * @var bool
     */
    private $parseConstants = false;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * YamlLinter constructor.
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function lint(SplFileInfo $file): LintErrorsCollection
    {
        $errors = new LintErrorsCollection();

        try {
            $content = $this->filesystem->readFromFileInfo($file);
            $this->parseYaml($content);
        } catch (ParseException $exception) {
            $exception->setParsedFile($file->getPathname());
            $errors[] = YamlLintError::fromParseException($exception);
        }

        return $errors;
    }

    /**
     * @throws ParseException
     */
    private function parseYaml(string $content): void
    {
        $flags = 0;
        $flags |= $this->objectSupport ? Yaml::PARSE_OBJECT : 0;
        $flags |= $this->exceptionOnInvalidType ? Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE : 0;
        $flags |= $this->parseConstants ? Yaml::PARSE_CONSTANT : 0;
        $flags |= $this->parseCustomTags ? Yaml::PARSE_CUSTOM_TAGS : 0;
        Yaml::parse($content, $flags);
    }

    public function isInstalled(): bool
    {
        return class_exists(Yaml::class);
    }

    public function setObjectSupport(bool $objectSupport): void
    {
        $this->objectSupport = $objectSupport;
    }

    public function setExceptionOnInvalidType(bool $exceptionOnInvalidType): void
    {
        $this->exceptionOnInvalidType = $exceptionOnInvalidType;
    }

    public function setParseCustomTags(bool $parseCustomTags): void
    {
        $this->parseCustomTags = $parseCustomTags;
    }

    public function setParseConstants(bool $parseConstants): void
    {
        $this->parseConstants = $parseConstants;
    }
}
