<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Finder\Expression\Expression;

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
            $expression = Expression::create($rule);
            $regex = $expression->getRegex();

            if ((bool) $config['case_insensitive']) {
                $regex->addOption('i');
            }

            if ((bool) $config['multiline']) {
                $regex->addOption('m');
            }

            if (!preg_match($regex->render(), $commitMessage)) {
                throw new RuntimeException(
                    sprintf('The commit message does not match the rule: %s', $rule)
                );
            }
        }
    }
}
