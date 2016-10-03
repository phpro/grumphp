<?php

namespace GrumPHP\Linter\Twig;

use GrumPHP\Linter\LintError;
use SplFileInfo;
use Twig_Error;

/**
 * Class TwigLintError
 *
 * @package GrumPHP\Linter\Twig
 */
class TwigLintError extends LintError
{

    /**
     * @param SplFileInfo $file
     * @param Twig_Error  $exception
     *
     * @return TwigLintError
     */
    public static function fromParsingException(SplFileInfo $file, Twig_Error $exception)
    {
        $message = sprintf('Parse error on line %u: %s', $exception->getTemplateLine(), $exception->getRawMessage());
        return new TwigLintError(LintError::TYPE_ERROR, $message, $file->getPathname(), 0);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '[%s] %s: %s',
            strtoupper($this->getType()),
            $this->getFile(),
            $this->getError()
        );
    }
}
