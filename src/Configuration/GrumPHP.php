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

    public function ignoreUnstagedChanges(): bool
    {
        return (bool) $this->container->getParameter('ignore_unstaged_changes');
    }

    public function getProcessAsyncLimit(): int
    {
        return (int) $this->container->getParameter('process_async_limit');
    }

    public function getProcessAsyncWaitTime(): int
    {
        return (int) $this->container->getParameter('process_async_wait');
    }

    public function getProcessTimeout(): ?float
    {
        $timeout = $this->container->getParameter('process_timeout');
        if (null === $timeout) {
            return null;
        }

        return (float) $timeout;
    }

    public function getTestSuites(): TestSuiteCollection
    {
        return $this->container->getParameter('grumphp.testsuites');
    }
}
