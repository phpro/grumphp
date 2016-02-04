<?php
namespace GrumPHP\Task\Php;

use GrumPHP\Task\AbstractParserTask;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Php Blacklist task
 */
class Blacklist extends AbstractParserTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'php_blacklist';
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
        $files = $context->getFiles()->name(sprintf('/\.(%s)$/i', implode('|', $config['triggered_by'])));
        if (0 === count($config['triggered_by']) || 0 === count($files)) {
            return;
        }
        $parseErrors = $this->parse($files, $config['keywords']);

        if ($parseErrors->count()) {
            throw new RuntimeException(sprintf(
                "You have blacklisted keywords in your commit:\n%s",
                $parseErrors->__toString()
            ));
        }
    }
}
