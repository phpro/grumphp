<?php
declare(strict_types=1);

namespace GrumPHPE2E;

use GrumPHP\Extension\ExtensionInterface;

class ValidateExtension implements ExtensionInterface
{
    public function imports(): iterable
    {
        yield __DIR__.DIRECTORY_SEPARATOR.'extension.yaml';
    }
}
