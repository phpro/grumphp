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
    public function getName(): string
    {
        return 'deptrac';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'depfile' => null,
            'formatter_graphviz' => false,
            'formatter_graphviz_display' => false,
            'formatter_graphviz_dump_image' => null,
            'formatter_graphviz_dump_dot' => null,
            'formatter_graphviz_dump_html' => null,
        ]);

        $resolver->addAllowedTypes('depfile', ['null', 'string']);
        $resolver->addAllowedTypes('formatter_graphviz', ['bool']);
        $resolver->addAllowedTypes('formatter_graphviz_display', ['bool']);
        $resolver->addAllowedTypes('formatter_graphviz_dump_image', ['null', 'string']);
        $resolver->addAllowedTypes('formatter_graphviz_dump_dot', ['null', 'string']);
        $resolver->addAllowedTypes('formatter_graphviz_dump_html', ['null', 'string']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        $files = $context->getFiles()->name('*.php');
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('deptrac');
        $arguments->add('analyze');
        $arguments->add('--formatter-graphviz='.(int) $config['formatter_graphviz']);
        $arguments->addOptionalArgument('--formatter-graphviz-display=%s', $config['formatter_graphviz_display']);
        $arguments->addOptionalArgument('--formatter-graphviz-dump-image=%s', $config['formatter_graphviz_dump_image']);
        $arguments->addOptionalArgument('--formatter-graphviz-dump-dot=%s', $config['formatter_graphviz_dump_dot']);
        $arguments->addOptionalArgument('--formatter-graphviz-dump-html=%s', $config['formatter_graphviz_dump_html']);
        $arguments->addOptionalArgument('%s', $config['depfile']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
