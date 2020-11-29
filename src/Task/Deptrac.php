<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Deptrac task.
 */
class Deptrac extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'depfile' => null,
            'formatter' => [],
            'graphviz_display' => true,
            'graphviz_dump_image' => null,
            'graphviz_dump_dot' => null,
            'graphviz_dump_html' => null,
        ]);

        $resolver->addAllowedTypes('depfile', ['null', 'string']);
        $resolver->addAllowedTypes('formatter', ['string[]']);
        $resolver->addAllowedTypes('graphviz_display', ['bool']);
        $resolver->addAllowedTypes('graphviz_dump_image', ['null', 'string']);
        $resolver->addAllowedTypes('graphviz_dump_dot', ['null', 'string']);
        $resolver->addAllowedTypes('graphviz_dump_html', ['null', 'string']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()->name('*.php');
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('deptrac');
        $arguments->add('analyze');
        $arguments->addArgumentArray('--formatter=%s', $config['formatter']);
        $arguments->add('--graphviz-display='.(int) $config['graphviz_display']);
        $arguments->addOptionalArgument('--graphviz-dump-image=%s', $config['graphviz_dump_image']);
        $arguments->addOptionalArgument('--graphviz-dump-dot=%s', $config['graphviz_dump_dot']);
        $arguments->addOptionalArgument('--graphviz-dump-html=%s', $config['graphviz_dump_html']);
        $arguments->addOptionalArgument('%s', $config['depfile']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
