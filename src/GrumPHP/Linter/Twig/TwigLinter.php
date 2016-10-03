<?php

namespace GrumPHP\Linter\Twig;

use Asm89\Twig\Lint\StubbedEnvironment;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use SplFileInfo;
use Twig_Error;
use Twig_Loader_String;

/**
 * Class TwigLinter
 *
 * @package GrumPHP\Linter\Twig
 */
class TwigLinter implements LinterInterface
{
    /**
     * @var StubbedEnvironment
     */
    private $environment;

    public function __construct()
    {
        $this->environment = new StubbedEnvironment(new Twig_Loader_String());
    }

    /**
     * @param SplFileInfo $file
     *
     * @return mixed
     */
    public function lint(SplFileInfo $file)
    {
        $errors = new LintErrorsCollection();

        try {
            $template = file_get_contents($file->getPathname());
            $tokens = $this->environment->tokenize($template, (string) $file);

            $this->environment->parse($tokens);
        } catch (Twig_Error $exception) {
            $errors->add(TwigLintError::fromParsingException($file, $exception));
        }

        return $errors;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return class_exists('Asm89\Twig\Lint\StubbedEnvironment');
    }
}
