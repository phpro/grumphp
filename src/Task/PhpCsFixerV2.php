<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Php-cs-fixer task v2
 */
class PhpCsFixerV2 extends AbstractPhpCsFixerTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpcsfixer2';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'allow_risky' => false,
            'cache_file' => null,
            'config' => null,
            'rules' => [],
            'using_cache' => true,
            'path_mode' => null,
            'verbose' => true,
            'diff' => false,
            'triggered_by' => ['php'],
        ]);

        $resolver->addAllowedTypes('allow_risky', ['bool']);
        $resolver->addAllowedTypes('cache_file', ['null', 'string']);
        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('rules', ['array']);
        $resolver->addAllowedTypes('using_cache', ['bool']);
        $resolver->addAllowedTypes('path_mode', ['null', 'string']);
        $resolver->addAllowedTypes('verbose', ['bool']);
        $resolver->addAllowedTypes('diff', ['bool']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        $resolver->setAllowedValues('path_mode', [null, 'override', 'intersection']);

        return $resolver;
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

        $this->formatter->resetCounter();

        $arguments = $this->processBuilder->createArgumentsForCommand('php-cs-fixer');
        $arguments->add('--format=json');
        $arguments->add('--dry-run');
        $arguments->addOptionalArgument('--allow-risky=%s', $config['allow_risky'] ? 'yes' : 'no');
        $arguments->addOptionalArgument('--cache-file=%s', $config['cache_file']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);

        if ($rules = $config['rules']) {
            $arguments->add(sprintf(
                '--rules=%s',
                // Comma-delimit rules if specified as a list; otherwise JSON-encode.
                array_values($rules) === $rules ? implode(',', $rules) : json_encode($rules)
            ));
        }

        $arguments->addOptionalArgument('--using-cache=%s', $config['using_cache'] ? 'yes' : 'no');
        $arguments->addOptionalArgument('--path-mode=%s', $config['path_mode']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->addOptionalArgument('--diff', $config['diff']);
        $arguments->add('fix');

        if ($context instanceof RunContext && $config['config'] !== null) {
            return $this->runOnAllFiles($context, $arguments);
        }

        return $this->runOnChangedFiles($context, $arguments, $files);
    }
}
