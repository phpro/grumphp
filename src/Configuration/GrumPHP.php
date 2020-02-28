<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Util\ComposerFile;
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

    public function stopOnFailure(): bool
    {
        return (bool) $this->container->getParameter('stop_on_failure');
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

    public function getAdditionalInfo(): ?string
    {
        return $this->container->getParameter('additional_info');
    }

    /**
     * Gets a value indicating whether the Git commit hook circumvention tip should be shown when a task fails.
     */
    public function hideCircumventionTip(): bool
    {
        return (bool) $this->container->getParameter('hide_circumvention_tip');
    }

    public function getTestSuites(): TestSuiteCollection
    {
        return $this->container->getParameter('grumphp.testsuites');
    }

    public function getAsciiContentPath(string $resource): ?string
    {
        if (null === $this->container->getParameter('ascii')) {
            return null;
        }

        $paths = $this->container->getParameter('ascii');
        if (!array_key_exists($resource, $paths)) {
            return null;
        }

        // Deal with multiple ascii files by returning one at random.
        if (\is_array($paths[$resource])) {
            shuffle($paths[$resource]);
            return reset($paths[$resource]);
        }

        return $paths[$resource];
    }
}
