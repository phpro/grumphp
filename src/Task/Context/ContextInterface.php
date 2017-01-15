<?php

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

interface ContextInterface
{
    /**
     * @return FilesCollection
     */
    public function getFiles();
}
