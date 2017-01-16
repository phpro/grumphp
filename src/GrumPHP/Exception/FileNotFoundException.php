<?php

namespace GrumPHP\Exception;

class FileNotFoundException extends RuntimeException
{
    /**
     * @param string $path
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('File "%s" doesn\'t exists.', $path));
    }
}
