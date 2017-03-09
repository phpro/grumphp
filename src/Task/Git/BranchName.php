<?php

namespace GrumPHP\Task\Git;

use Gitonomy\Git\Reference\Branch;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Regex;
use GrumPHP\Exception\RuntimeException;

/**
 * Git BranchName Task
 */
class BranchName extends AbstractRegex
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'git_branch_name';
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context)
    {
        return $context instanceof RunContext;
    }

    /**
     * @param array $config
     * @param string $commitMessage
     * @param string $rule
     * @param string $ruleName
     *
     * @throws RuntimeException
     */
    protected function runMatcher(array $config, $commitMessage, $rule, $ruleName)
    {
        $regex = new Regex($rule);

        if ((bool) $config['case_insensitive']) {
            $regex->addPatternModifier('i');
        }

        $additionalModifiersArray = array_filter(str_split((string) $config['additional_modifiers']));
        array_map([$regex, 'addPatternModifier'], $additionalModifiersArray);

        if (!preg_match((string) $regex, $commitMessage)) {
            throw new RuntimeException("Rule not matched: \"$ruleName\" $rule");
        }
    }

    /**
     * @param ContextInterface|RunContext $context
     *
     * @return TaskResult
     */
    public function run(ContextInterface $context)
    {
        $gitRepository = $this->grumPHP->getGitRepository();
        $branch = new Branch($gitRepository, $gitRepository->getHead()->getRevision());
        $name = $branch->getName();
        $config = $this->getConfiguration();
        $exceptions = [];

        foreach ($config['matchers'] as $ruleName => $rule) {
            try {
                $this->runMatcher($config, $name, $rule, $ruleName);
            } catch (RuntimeException $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (count($exceptions)) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $exceptions));
        }

        return TaskResult::createPassed($this, $context);
    }
}
