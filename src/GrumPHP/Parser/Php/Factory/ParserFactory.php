<?php

namespace GrumPHP\Parser\Php\Factory;

use GrumPHP\Parser\Php\PhpParser;
use PhpParser\ParserFactory as PhpParserFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ParserFactory
 *
 * @package GrumPHP\Parser\Php\Factory
 */
class ParserFactory
{
    /**
     * @param array $options
     *
     * @return \PhpParser\Parser
     */
    public function createFromOptions(array $options)
    {
        $config = $this->getConfigurableOptions()->resolve($options);
        $kind = ($config['kind'] === PhpParser::KIND_PHP5)
            ? PhpParserFactory::PREFER_PHP5 : PhpParserFactory::PREFER_PHP7;

        return (new PhpParserFactory())->create($kind);
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired('kind');
        $resolver->setAllowedTypes('kind', 'string');
        $resolver->setAllowedValues('kind', [PhpParser::KIND_PHP5, PhpParser::KIND_PHP7]);

        return $resolver;
    }
}
