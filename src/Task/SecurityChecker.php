<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
class SecurityChecker extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'lockfile' => './composer.lock',
            'format' => null,
            'end_point' => null,
            'timeout' => null,
            'run_always' => false,
            'allow-list' => [],
        ]);

        $resolver->addAllowedTypes('lockfile', ['string']);
        $resolver->addAllowedTypes('format', ['null', 'string']);
        $resolver->addAllowedTypes('end_point', ['null', 'string']);
        $resolver->addAllowedTypes('timeout', ['null', 'int']);
        $resolver->addAllowedTypes('run_always', ['bool']);
        $resolver->addAllowedTypes('allow-list', ['array']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        return TaskResult::createFailed(
            $this,
            $context,
            'The securitychecker task is discontinued by SensioLabs.'.PHP_EOL
            . 'Please consider switching to one of the following tasks instead:'.PHP_EOL.PHP_EOL
            . '- securitychecker_enlightn '
            . '(https://github.com/phpro/grumphp/blob/master/doc/tasks/securitychecker/enlightn.md)'.PHP_EOL
            . '- securitychecker_local '
            . '(https://github.com/phpro/grumphp/blob/master/doc/tasks/securitychecker/local.md)'.PHP_EOL
            . '- securitychecker_symfony '
            . '(https://github.com/phpro/grumphp/blob/master/doc/tasks/securitychecker/symfony.md)'.PHP_EOL
        );
    }
}
