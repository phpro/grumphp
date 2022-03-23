<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwigCs extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'path' => '.',
            'severity' => 'warning',
            'display' => 'all',
            'ruleset' => 'FriendsOfTwig\Twigcs\Ruleset\Official',
            'triggered_by' => ['twig'],
            'exclude' => [],
        ]);

        $resolver->addAllowedTypes('path', ['string']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('severity', ['string']);
        $resolver->addAllowedTypes('display', ['string']);
        $resolver->addAllowedTypes('ruleset', ['string']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('twigcs');

        $arguments->addOptionalArgument('--severity=%s', $config['severity']);
        $arguments->addOptionalArgument('--display=%s', $config['display']);
        $arguments->addOptionalArgument('--ruleset=%s', $config['ruleset']);
        $arguments->addOptionalArgument('--ansi', true);

        // removes all NULL, FALSE and Empty Strings
        $exclude = array_filter(
            $config['exclude'],
            /**
             * @param mixed $exclude
             */
            static fn ($exclude): bool => $exclude && $exclude !== ''
        );
        $arguments->addArgumentArray('--exclude=%s', $exclude);

        if ($context instanceof GitPreCommitContext) {
            $arguments->addFiles($files);
        }

        if ($context instanceof RunContext) {
            $arguments->add($config['path']);
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
