<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileSize implements TaskInterface
{
    protected $grumPHP;

    public function __construct(GrumPHP $grumPHP)
    {
        $this->grumPHP = $grumPHP;
    }

    public function getName(): string
    {
        return 'file_size';
    }

    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'max_size' => '10M',
            'ignore_patterns' => [],
        ]);

        $resolver->addAllowedTypes('max_size', ['string', 'integer']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        if (0 === $context->getFiles()->count()) {
            return TaskResult::createSkipped($this, $context);
        }

        $maxSize = $config['max_size'];
        $files = $context->getFiles()
            ->ignoreSymlinks()
            ->notPaths($config['ignore_patterns'])
            ->size(sprintf('>%s', $maxSize));

        if ($files->count() > 0) {
            $errorMessage = 'Large files detected:'.PHP_EOL;

            foreach ($files as $file) {
                $errorMessage .= sprintf(
                    '- %s exceeded the maximum size of %s.'.PHP_EOL,
                    $file->getFilename(),
                    $maxSize
                );
            }

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }
}
