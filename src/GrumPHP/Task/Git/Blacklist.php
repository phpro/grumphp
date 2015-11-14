<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;

/**
 * Blacklist task
 *
 * @author  Igor Mukhin <igor.mukhin@gmail.com>
 */
class Blacklist extends AbstractExternalTask
{
    const COMMAND_NAME = 'git';

    /**
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'keywords' => null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandLocation()
    {
        return $this->externalCommandLocator->locate(self::COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();
        if (empty($config['keywords'])) {
            return;
        }

        $arguments = ProcessArgumentsCollection::forExecutable($this->getCommandLocation());
        $arguments->add('grep');
        $arguments->add('--cached');
        $arguments->add('-n');
        $arguments->addArgumentArray('-e %s', $config['keywords']);
        $arguments->addFiles($files);

        $this->processBuilder->setArguments($arguments->getValues());
        $process = $this->processBuilder->getProcess();
        $process->run();

        if ($process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                "You have blacklisted keywords in your commit:\n%s",
                $process->getOutput()
            ));
        }
    }
}
