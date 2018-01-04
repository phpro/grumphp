<?php declare(strict_types=1);

namespace GrumPHP\Task\Git;

use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\Repository;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Util\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    public function __construct(GrumPHP $grumPHP, Repository $repository)
    {
        $this->grumPHP = $grumPHP;
        $this->repository = $repository;
    }

    public function getName(): string
    {
        return 'git_branch_name';
    }

    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
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

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    /**
     * @throws RuntimeException
     */
    private function runMatcher(array $config, string $name, string $rule, string $ruleName)
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
    public function run(ContextInterface $context): TaskResultInterface
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
                $this->runMatcher($config, $name, $rule, (string) $ruleName);
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
