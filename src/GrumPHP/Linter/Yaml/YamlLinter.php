<?php

namespace GrumPHP\Linter\Yaml;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use SplFileInfo;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlLinter
 *
 * @package GrumPHP\Linter\Yaml
 */
class YamlLinter implements LinterInterface
{

    /**
     * True if object support is enabled, false otherwise
     *
     * @var bool
     */
    private $objectSupport = false;

    /**
     * True if an exception must be thrown on invalid types false otherwise
     *
     * @var bool
     */
    private $exceptionOnInvalidType = false;

    /**
     * @param SplFileInfo $file
     *
     * @return LintErrorsCollection
     */
    public function lint(SplFileInfo $file)
    {
        $errors = new LintErrorsCollection();

        try {
            $content = file_get_contents($file->getPathname());
            $this->parseYaml($content);
        } catch (ParseException $exception) {
            $exception->setParsedFile($file->getPathname());
            $errors[] = YamlLintError::fromParseException($exception);
        }

        return $errors;
    }

    /**
     * @param string $content
     * @throws ParseException
     */
    private function parseYaml($content)
    {
        $rc = new \ReflectionClass('Symfony\Component\Yaml\Yaml');
        $method = $rc->getMethod('parse');
        $params = $method->getParameters();

        // Lint on Symfony Yaml < 3.1
        if ($params[1]->getName() !== 'flags') {
            Yaml::parse($content, $this->exceptionOnInvalidType, $this->objectSupport);
            return;
        }

        // Lint on Symfony Yaml >= 3.1
        $flags = 0;
        $flags += $this->objectSupport ? Yaml::PARSE_OBJECT : 0;
        $flags += $this->exceptionOnInvalidType ? Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE : 0;
        Yaml::parse($content, $flags);
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return class_exists('Symfony\Component\Yaml\Yaml');
    }

    /**
     * @param boolean $objectSupport
     */
    public function setObjectSupport($objectSupport)
    {
        $this->objectSupport = $objectSupport;
    }

    /**
     * @param boolean $exceptionOnInvalidType
     */
    public function setExceptionOnInvalidType($exceptionOnInvalidType)
    {
        $this->exceptionOnInvalidType = $exceptionOnInvalidType;
    }
}
