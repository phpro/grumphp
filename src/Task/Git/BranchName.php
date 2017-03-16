<?php

namespace GrumPHP\Task\Git;

use Gitonomy\Git\Exception\ProcessException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Regex;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Gitonomy\Git\Repository;

/**
 * Git BranchName Task
 */
class BranchName implements TaskInterface
{

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param GrumPHP $grumPHP
     */
    public function __construct(GrumPHP $grumPHP, Repository $repository)
    {
        $this->grumPHP = $grumPHP;
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'git_branch_name';
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
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'matchers' => [],
            'additional_modifiers' => '',
            'allow_detached_head' => true,
        ]);

        $resolver->addAllowedTypes('matchers', ['array']);
        $resolver->addAllowedTypes('additional_modifiers', ['string']);
        $resolver->addAllowedTypes('allow_detached_head', ['boolean']);

        return $resolver;
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context)
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    /**
     * @param array $config
     * @param string $name
     * @param string $rule
     * @param string $ruleName
     *
     * @throws RuntimeException
     */
    private function runMatcher(array $config, $name, $rule, $ruleName)
    {
        $regex = new Regex($rule);

        $additionalModifiersArray = array_filter(str_split($config['additional_modifiers']));
        array_map([$regex, 'addPatternModifier'], $additionalModifiersArray);

        if (!preg_match((string) $regex, $name)) {
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
        $config = $this->getConfiguration();
        $exceptions = [];

        try {
            $name = trim($this->repository->run('symbolic-ref', ['HEAD', '--short']));
        } catch (ProcessException $e) {
            if ($config['allow_detached_head']) {
                return TaskResult::createPassed($this, $context);
            }
            $message = "Branch naming convention task is not allowed on a detached HEAD.";
            return TaskResult::createFailed($this, $context, $message);
        }

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
