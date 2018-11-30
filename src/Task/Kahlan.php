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
 * Phpspec task.
 */
class Kahlan extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'kahlan';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config' => 'kahlan-config.php',
            'src' => ['src'],
            'spec' => ['spec'],
            'pattern' => '*Spec.php',
            'reporter' => null,
            'coverage' => null,
            'clover' => null,
            'istanbul' => null,
            'lcov' => null,
            'ff' => 0,
            'no_colors' => false,
            'no_header' => false,
            'include' => ['*'],
            'exclude' => [],
            'persistent' => true,
            'cc' => false,
            'autoclear' => [
                'Kahlan\Plugin\Monkey',
                'Kahlan\Plugin\Call',
                'Kahlan\Plugin\Stub',
                'Kahlan\Plugin\Quit',
            ],
        ]);

        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('src', ['array']);
        $resolver->addAllowedTypes('spec', ['array']);
        $resolver->addAllowedTypes('pattern', ['string']);
        $resolver->addAllowedTypes('reporter', ['null', 'string']);
        $resolver->addAllowedTypes('coverage', ['null', 'string', 'int']);
        $resolver->addAllowedTypes('clover', ['null', 'string']);
        $resolver->addAllowedTypes('istanbul', ['null', 'string']);
        $resolver->addAllowedTypes('lcov', ['null', 'string']);
        $resolver->addAllowedTypes('ff', ['int']);
        $resolver->addAllowedTypes('no_colors', ['bool']);
        $resolver->addAllowedTypes('no_header', ['bool']);
        $resolver->addAllowedTypes('include', ['array']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('persistent', ['bool']);
        $resolver->addAllowedTypes('cc', ['bool']);
        $resolver->addAllowedTypes('autoclear', ['null', 'array']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $files = $context->getFiles()->name('*.php');
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('kahlan');
        $arguments->addOptionalArgument('config', $config['config']);
        $arguments->addArgumentArrayWithSeparatedValue('src', $config['src']);
        $arguments->addArgumentArrayWithSeparatedValue('spec', $config['spec']);
        $arguments->addOptionalArgument('--pattern', $config['pattern']);
        $arguments->addOptionalArgument('--reporter', $config['reporter']);
        $arguments->addOptionalArgument('--coverage', $config['coverage']);
        $arguments->addOptionalArgument('--clover', $config['clover']);
        $arguments->addOptionalArgument('--istanbul', $config['istanbul']);
        $arguments->addOptionalArgument('--lcov', $config['lcov']);
        $arguments->addOptionalArgument('--ff', $config['ff']);
        $arguments->addOptionalArgument('--no-colors', $config['no_colors']);
        $arguments->addOptionalArgument('--no-header', $config['no_header']);
        $arguments->addOptionalArgument('--include', $config['no_header']);
        $arguments->addOptionalArgument('--exclude', $config['no_header']);
        $arguments->addOptionalArgument('--persistent', $config['persistent']);
        $arguments->addOptionalArgument('--cc', $config['cc']);
        $arguments->addOptionalArgument('--autoclear', $config['autoclear']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
