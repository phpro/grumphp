<?php declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ComposerRequireChecker task
 */
class ComposerRequireChecker extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'composer_require_checker';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'composer_file' => 'composer.json',
            'config_file' => null,
            'triggered_by' => ['composer.json', 'composer.lock', '*.php'],
        ]);

        $resolver->addAllowedTypes('composer_file', ['string']);
        $resolver->addAllowedTypes('config_file', ['null', 'string']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();
        $files = $context->getFiles()->names($config['triggered_by']);

        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('composer-require-checker');

        $arguments->add('check');
        $arguments->addOptionalArgument('--config-file=%s', $config['config_file']);
        $arguments->add('--no-interaction');
        $arguments->add($config['composer_file']);

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
