<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'keywords' => array(),
            'triggered_by' => array('php')
        ));

        $resolver->addAllowedTypes('keywords', array('array'));
        $resolver->addAllowedTypes('triggered_by', array('array'));

        return $resolver;
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
        $config = $this->getConfiguration();
        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === count($files) || empty($config['keywords'])) {
            return;
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('git');
        $arguments->add('grep');
        $arguments->add('--cached');
        $arguments->add('-n');
        $arguments->addArgumentArrayWithSeparatedValue('-e', $config['keywords']);
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
