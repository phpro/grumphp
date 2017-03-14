<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
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
          'additional_modifiers' => ''
        ]);

        $resolver->addAllowedTypes('matchers', ['array']);
        $resolver->addAllowedTypes('additional_modifiers', ['string']);

        return $resolver;
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
     * @param string $name
     * @param string $rule
     * @param string $ruleName
     *
     * @throws RuntimeException
     */
    private function runMatcher(array $config, $name, $rule, $ruleName)
    {
        $regex = new Regex($rule);

        $additionalModifiersArray = array_filter(str_split((string) $config['additional_modifiers']));
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
        $name = trim($this->repository->run('symbolic-ref', ['HEAD', '--short']));
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
