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
     * This method can be used to determine the Symfony Linter version.
     * If this method returns true, you are using Symfony YAML > 3.1.
     *
     * @see http://symfony.com/blog/new-in-symfony-3-1-customizable-yaml-parsing-and-dumping
     */
    public static function supportsFlags(): bool
    {
        $rc = new ReflectionClass(Yaml::class);
        $method = $rc->getMethod('parse');
        $params = $method->getParameters();

        return 'flags' === $params[1]->getName();
    }

    /**
     * This method can be used to determine the Symfony Linter version.
     * If this method returns true, you are using Symfony YAML >= 4.0.0.
     *
     * @see http://symfony.com/blog/new-in-symfony-3-1-yaml-deprecations#deprecated-the-dumper-setindentation-method
     */
    public static function supportsTagsWithoutColon(): bool
    {
        return !method_exists(Dumper::class, 'setIndentation');
    }

    /**
     * @throws ParseException
     */
    private function parseYaml(string $content)
    {
        // Lint on Symfony Yaml < 3.1
        if (!self::supportsFlags()) {
            Yaml::parse($content, $this->exceptionOnInvalidType, $this->objectSupport);

            return;
        }

        // Lint on Symfony Yaml >= 3.1
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

    public function setObjectSupport(bool $objectSupport)
    {
        $this->objectSupport = $objectSupport;
    }

    public function setExceptionOnInvalidType(bool $exceptionOnInvalidType)
    {
        $this->exceptionOnInvalidType = $exceptionOnInvalidType;
    }

    public function setParseCustomTags(bool $parseCustomTags)
    {
        // Yaml::PARSE_CONSTANT is only available in Symfony Yaml >= 3.2
        $this->parseCustomTags = $parseCustomTags && defined('Symfony\Component\Yaml\Yaml::PARSE_CONSTANT');
    }

    public function setParseConstants(bool $parseConstants)
    {
        // Yaml::PARSE_CUSTOM_TAGS is only available in Symfony Yaml >= 3.3
        $this->parseConstants = $parseConstants && defined('Symfony\Component\Yaml\Yaml::PARSE_CUSTOM_TAGS');
    }
}
