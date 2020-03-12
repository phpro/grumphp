<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\ParserInterface;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractParserTask implements TaskInterface
{
    /**
     * @var TaskConfigInterface
     */
    protected $configuration;

    /**
     * @var ParserInterface
     */
    protected $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->configuration = new EmptyTaskConfig();
        $this->parser = $parser;

        if (!$parser->isInstalled()) {
            throw new RuntimeException(
                sprintf(
                    'The %s can\'t run on your system. Please install all dependencies.',
                    $this->getConfig()->getName()
                )
            );
        }
    }

    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'triggered_by' => [],
            'ignore_patterns' => [],
        ]);

        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);

        return $resolver;
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->configuration;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->configuration = $config;

        return $new;
    }

    protected function parse(FilesCollection $files): ParseErrorsCollection
    {
        // Skip ignored patterns:
        $configuration = $this->getConfig()->getOptions();
        foreach ($configuration['ignore_patterns'] as $pattern) {
            $files = $files->notPath($pattern);
        }

        // Parse every file:
        $parseErrors = new ParseErrorsCollection();
        foreach ($files as $file) {
            foreach ($this->parser->parse($file) as $error) {
                $parseErrors->add($error);
            }
        }

        return $parseErrors;
    }
}
