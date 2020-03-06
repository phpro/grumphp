<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Collection\TestSuiteCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @deprecated  : Directly inject parameters instead or move to new specialized class.
 */
class GrumPHP
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getHooksDir(): ?string
    {
        return $this->container->getParameter('hooks_dir');
    }

    public function getHooksPreset(): string
    {
        return $this->container->getParameter('hooks_preset');
    }

    public function getGitHookVariables(): array
    {
        return $this->container->getParameter('git_hook_variables');
    }
}
