<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Util\Regex;

/**
 * Git CommitMessage Task
 *
 * @package GrumPHP\Task
 */
class CommitMessage implements TaskInterface
{
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
        return 'git_commit_message';
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return array_merge(
            $this->getDefaultConfiguration(),
            $this->grumPHP->getTaskConfiguration($this->getName())
        );
    }

    /**
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'case_insensitive' => true,
            'multiline' => true,
            'matchers' => array(),
        );
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
        $config = $this->getConfiguration();
        $commitMessage = $context->getCommitMessage();

        foreach ($config['matchers'] as $rule) {
            $regex = new Regex($rule);

            if ((bool) $config['case_insensitive']) {
                $regex->addPatternModifier('i');
            }

            if ((bool) $config['multiline']) {
                $regex->addPatternModifier('m');
            }

            if (!preg_match($regex->__toString(), $commitMessage)) {
                throw new RuntimeException(
                    sprintf('The commit message does not match the rule: %s', $rule)
                );
            }
        }
    }
}
