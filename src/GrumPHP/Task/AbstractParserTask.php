<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\NodeVisitorsCollection;
use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\ParserInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpParser\NodeVisitor;

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
     * @var NodeVisitorsCollection|NodeVisitor[]
     */
    private $nodeVisitors;

    /**
     * @param GrumPHP         $grumPHP
     * @param ParserInterface $parser
     */
    public function __construct(GrumPHP $grumPHP, ParserInterface $parser)
    {
        $this->grumPHP = $grumPHP;
        $this->parser  = $parser;

        $this->nodeVisitors = new NodeVisitorsCollection();
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'keywords'        => array(),
            'visitors'        => array(),
            'triggered_by'    => array(),
            'ignore_patterns' => array(),
        ));

        $resolver->addAllowedTypes('keywords', array('array'));
        $resolver->addAllowedTypes('visitors', array('array'));
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
    protected function parse(FilesCollection $files)
    {
        $this->guardParserIsInstalled();

        // Skip ignored patterns:
        $configuration = $this->getConfiguration();
        foreach ($configuration['ignore_patterns'] as $pattern) {
            $files = $files->notPath($pattern);
        }

        $keywords = $configuration['keywords'];
        $visitors = $this->getNodeVisitors();

        // Parse every file:
        $parseErrors = new ParseErrorsCollection();
        foreach ($files as $file) {
            foreach ($this->parser->parse($file, $keywords, $visitors) as $error) {
                $parseErrors->add($error);
            }
        }

        return $parseErrors;
    }


    /**
     * @param NodeVisitor $visitor
     */
    public function addNodeVisitor(NodeVisitor $visitor)
    {
        if ($this->nodeVisitors->contains($visitor)) {
            return;
        }

        $this->nodeVisitors->add($visitor);
    }

    /**
     * @return NodeVisitorsCollection|NodeVisitor[]
     */
    public function getNodeVisitors()
    {
        return $this->nodeVisitors;
    }
}
