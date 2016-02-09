<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\ParserInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractParser
 *
 * @package GrumPHP\Task
 */
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
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'keywords'        => array(),
            'triggered_by'    => array(),
            'ignore_patterns' => array(),
        ));

        $resolver->addAllowedTypes('keywords', array('array'));
        $resolver->addAllowedTypes('triggered_by', array('array'));
        $resolver->addAllowedTypes('ignore_patterns', array('array'));

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
     * Validates if the parser is installed.
     *
     * @throws RuntimeException
     */
    protected function guardParserIsInstalled()
    {
        if (!$this->parser->isInstalled()) {
            throw new RuntimeException(
                sprintf('The %s can\'t run on your system. Please install all dependencies.', $this->getName())
            );
        }
    }

    /**
     * @param FilesCollection $files
     *
     * @return ParseErrorsCollection
     */
    protected function parse(FilesCollection $files, array $keywords)
    {
        $this->guardParserIsInstalled();

        // Skip ignored patterns:
        $configuration = $this->getConfiguration();
        foreach ($configuration['ignore_patterns'] as $pattern) {
            $files = $files->notPath($pattern);
        }

        // Parse every file:
        $parseErrors = new ParseErrorsCollection();
        foreach ($files as $file) {
            $filename = $this->grumPHP->getGitDir() . '/' . $file->getRelativePathname();
            foreach ($this->parser->parse($filename, $keywords) as $error) {
                $parseErrors->add($error);
            }
        }

        return $parseErrors;
    }
}
