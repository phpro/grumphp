<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Util\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Git TrailingPeriod Task
 */
class TrailingPeriod implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    private $grumPHP;

    /**
     * @param GrumPHP $grumPHP
     */
    public function __construct(GrumPHP $grumPHP)
    {
        $this->grumPHP = $grumPHP;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'git_trailing_period';
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        return new OptionsResolver();
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context)
    {
        return $context instanceof GitCommitMsgContext;
    }

    /**
     * @param ContextInterface|GitCommitMsgContext $context
     */
    public function run(ContextInterface $context)
    {
        $commitMessage = $context->getCommitMessage();

        if (trim($commitMessage) === '') {
            return TaskResult::createPassed($this, $context);
        }

        $commitMessage = str_replace("\r", '', $commitMessage);
        $lines = explode("\n", $commitMessage);

        if (mb_substr(rtrim($lines[0]), -1) === '.') {
            return TaskResult::createFailed(
                $this,
                $context,
                'Please omit trailing period from commit message subject.'
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
