<?php

namespace GrumPHP\Task;

/**
 * @property \GrumPHP\Formatter\PhpcsFormatter $formatter
 */
class PhpCbf extends PhpCsParallel
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'phpcbf';
    }

    /**
     * @return string
     */
    public function getExecutableName(): string
    {
        return 'phpcbf';
    }
}
