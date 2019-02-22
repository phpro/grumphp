<?php

declare(strict_types=1);

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

interface ContextInterface
{
    public function getFiles(): FilesCollection;
}
