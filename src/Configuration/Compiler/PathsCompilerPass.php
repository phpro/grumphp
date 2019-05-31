<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PathsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // TODO : set git_dir based on GitDirLocator
        // todo : set bin_dir automatically based on composer file.
        // todo : when git is not found -> throw exception.
        // todo : when composer is not found -> ask path to user

    }
}
