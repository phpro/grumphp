<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Shell task
 */
class Shell extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'shell';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'scripts' => [],
            'triggered_by' => ['php'],
            'include_files' => false,
            'include_files_with_comma' => true,
            'include_files_parameter' => null,
        ]);

        $resolver->addAllowedTypes('scripts', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('include_files', ['bool']);
        $resolver->addAllowedTypes('include_files_with_comma', ['bool']);
        $resolver->addAllowedTypes('include_files_parameter', ['null', 'string']);

        $resolver->setNormalizer('scripts', function ($resolver, $scripts) {
            return array_map(function ($script) {
                return is_string($script) ? (array) $script : $script;
            }, $scripts);
        });

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();
        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $exceptions = [];
        foreach ($config['scripts'] as $script) {
            try {
                $this->runShell($script, $files);
            } catch (RuntimeException $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (count($exceptions)) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $exceptions));
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param array $scriptArguments
     * @param FilesCollection $files
     */
    private function runShell(array $scriptArguments, FilesCollection $files)
    {
        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('sh');
        $arguments->addArgumentArray('%s', $scriptArguments);

        if ($config['include_files']) {
            if ($config['include_files_parameter'] !== null) {
                $arguments->addArgumentWithCommaSeparatedFiles($config['include_files_parameter'] . '=%s', $files);
            } else {
                if ($config['include_files_with_comma']) {
                    $arguments->addCommaSeparatedFiles($files);
                } else {
                    $arguments->addFiles($files);
                }
            }
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($this->formatter->format($process));
        }
    }
}
