<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\ParserInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractParserTask implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @param GrumPHP         $grumPHP
     * @param ParserInterface $parser
     */
    public function __construct(GrumPHP $grumPHP, ParserInterface $parser)
    {
        $this->grumPHP = $grumPHP;
        $this->parser  = $parser;

        if (!$parser->isInstalled()) {
            throw new RuntimeException(
                sprintf('The %s can\'t run on your system. Please install all dependencies.', $this->getName())
            );
        }
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'triggered_by'     => [],
            'ignore_patterns'  => [],
        ]);

        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * @param FilesCollection $files
     *
     * @return ParseErrorsCollection
     */
    protected function parse(FilesCollection $files)
    {
        // Skip ignored patterns:
        $configuration = $this->getConfiguration();
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
