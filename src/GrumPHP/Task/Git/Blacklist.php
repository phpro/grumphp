<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;

/**
 * Git Blacklist Task
 *
 * @package GrumPHP\Task\Git
 */
class Blacklist extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'git_blacklist';
    }

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

        $arguments = $this->processBuilder->createArgumentsForCommand('git');
        $arguments->add('grep');
        $arguments->add('--cached');
        $arguments->add('-n');
        $arguments->addArgumentArray('-e %s', $config['keywords']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if ($process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                "You have blacklisted keywords in your commit:\n%s",
                $process->getOutput()
            ));
        }
    }
}
