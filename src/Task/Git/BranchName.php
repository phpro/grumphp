<?php

declare(strict_types=1);

namespace GrumPHP\Task\Git;

use Gitonomy\Git\Exception\ProcessException;
use GrumPHP\Git\GitRepository;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Regex;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BranchName implements TaskInterface
{
    /**
     * @var TaskConfigInterface
     */
    private $config;

    /**
     * @var GitRepository
     */
    private $repository;

    public function __construct(GitRepository $repository)
    {
        $this->config = new EmptyTaskConfig();
        $this->repository = $repository;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'blacklist' => [],
            'whitelist' => [],
            'additional_modifiers' => '',
            'allow_detached_head' => true,
        ]);

        $resolver->addAllowedTypes('blacklist', ['array']);
        $resolver->addAllowedTypes('whitelist', ['array']);
        $resolver->addAllowedTypes('additional_modifiers', ['string']);
        $resolver->addAllowedTypes('allow_detached_head', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();
        $errors = [];

        try {
            $name = trim((string) $this->repository->run('symbolic-ref', ['HEAD', '--short']));
        } catch (ProcessException $e) {
            if ($config['allow_detached_head']) {
                return TaskResult::createPassed($this, $context);
            }
            $message = 'Branch naming convention task is not allowed on a detached HEAD.';

            return TaskResult::createFailed($this, $context, $message);
        }

        foreach ($config['blacklist'] as $rule) {
            $regex = new Regex($rule);

            $additionalModifiersArray = array_filter(str_split($config['additional_modifiers']));
            array_map([$regex, 'addPatternModifier'], $additionalModifiersArray);

            if (preg_match((string)$regex, $name)) {
                $errors[] = sprintf('Matched blacklist rule: %s', $rule);
            }
        }
        foreach ($config['whitelist'] as $rule) {
            $regex = new Regex($rule);

            $additionalModifiersArray = array_filter(str_split($config['additional_modifiers']));
            array_map([$regex, 'addPatternModifier'], $additionalModifiersArray);

            if (!preg_match((string) $regex, $name)) {
                $errors[] = sprintf('Whitelist rule not matched: %s', $rule);
            }
        }

        if (\count($errors)) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $errors));
        }

        return TaskResult::createPassed($this, $context);
    }
}
