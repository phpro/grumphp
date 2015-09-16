<?php

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

/**
 * Interface ContextInterface
 *
 * @package GrumPHP\Context
 */
interface ContextInterface
{

    /**
     * @return FilesCollection
     */
    public function getFiles();
}
