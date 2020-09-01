<?php

declare(strict_types=1);

namespace GrumPHP\Task\Git;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Git\GitRepository;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Util\Regex;
use GrumPHP\Util\Str;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitMessage implements TaskInterface
{
    /**
     * @var TaskConfigInterface
     */
    private $config;

    /**
     * @var GitRepository
     */
    private $repository;

    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_punctuations' => false,
            'enforce_no_subject_trailing_period' => true,
            'enforce_single_lined_subject' => true,
            'max_body_width' => 72,
            'max_subject_width' => 60,
            'case_insensitive' => true,
            'multiline' => true,
            'type_scope_conventions' => [],
            'matchers' => [],
            'additional_modifiers' => '',
        ]);

        $resolver->addAllowedTypes('allow_empty_message', ['bool']);
        $resolver->addAllowedTypes('type_scope_conventions', ['array']);
        $resolver->addAllowedTypes('enforce_capitalized_subject', ['bool']);
        $resolver->addAllowedTypes('enforce_no_subject_punctuations', ['bool']);
        $resolver->addAllowedTypes('enforce_no_subject_trailing_period', ['bool']);
        $resolver->addAllowedTypes('enforce_single_lined_subject', ['bool']);
        $resolver->addAllowedTypes('max_body_width', ['int']);
        $resolver->addAllowedTypes('max_subject_width', ['int']);
        $resolver->addAllowedTypes('case_insensitive', ['bool']);
        $resolver->addAllowedTypes('multiline', ['bool']);
        $resolver->addAllowedTypes('matchers', ['array']);
        $resolver->addAllowedTypes('additional_modifiers', ['string']);

        return $resolver;
    }

    public function __construct(GitRepository $repository)
    {
        $this->repository = $repository;
        $this->config = new EmptyTaskConfig();
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitCommitMsgContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        assert($context instanceof GitCommitMsgContext);

        $config = $this->getConfig()->getOptions();
        $commitMessage = $context->getCommitMessage();
        $exceptions = [];

        if (!(bool) $config['allow_empty_message'] && '' === trim($commitMessage)) {
            return TaskResult::createFailed(
                $this,
                $context,
                'Commit message should not be empty.'
            );
        }

        if ((bool) $config['enforce_capitalized_subject'] && !$this->subjectIsCapitalized($context)) {
            return TaskResult::createFailed(
                $this,
                $context,
                'Subject should start with a capital letter.'
            );
        }

        if ((bool) $config['enforce_single_lined_subject'] && !$this->subjectIsSingleLined($context)) {
            return TaskResult::createFailed(
                $this,
                $context,
                'Subject should be one line and followed by a blank line.'
            );
        }

        if ((bool) $config['enforce_no_subject_punctuations'] && $this->subjectHasPunctuations($context)) {
            return TaskResult::createFailed(
                $this,
                $context,
                'Please omit all punctuations from commit message subject.'
            );
        }

        if ((bool) $config['enforce_no_subject_trailing_period'] && $this->subjectHasTrailingPeriod($context)) {
            return TaskResult::createFailed(
                $this,
                $context,
                'Please omit trailing period from commit message subject.'
            );
        }


        if ((bool) $this->enforceTypeScopeConventions()) {
            try {
                $this->checkTypeScopeConventions($context);
            } catch (RuntimeException $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        foreach ($config['matchers'] as $ruleName => $rule) {
            try {
                $this->runMatcher($config, $commitMessage, $rule, (string) $ruleName);
            } catch (RuntimeException $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (\count($exceptions)) {
            return TaskResult::createFailed(
                $this,
                $context,
                implode(PHP_EOL, $exceptions).PHP_EOL.sprintf(
                    'Original commit message: %s%s',
                    PHP_EOL,
                    $commitMessage
                )
            );
        }

        return $this->enforceTextWidth($context);
    }

    private function enforceTextWidth(GitCommitMsgContext $context): TaskResult
    {
        $commitMessage = $context->getCommitMessage();
        $config = $this->getConfig()->getOptions();

        if ('' === trim($commitMessage)) {
            return TaskResult::createPassed($this, $context);
        }

        $errors = [];
        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);

        $subject = rtrim($lines[0]);
        if ($config['max_subject_width'] > 0) {
            $maxSubjectWidth = $config['max_subject_width'] + $this->getSpecialPrefixLength($subject);

            if (mb_strlen($subject) > $maxSubjectWidth) {
                $errors[] = sprintf('Please keep the subject <= %u characters.', $maxSubjectWidth);
            }
        }

        if ($config['max_body_width'] > 0) {
            foreach (\array_slice($lines, 2) as $index => $line) {
                if (mb_strlen(rtrim($line)) > $config['max_body_width']) {
                    $errors[] = sprintf(
                        'Line %u of commit message has > %u characters.',
                        (int)($index) + 3,
                        $config['max_body_width']
                    );
                }
            }
        }

        if (\count($errors)) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $errors));
        }

        return TaskResult::createPassed($this, $context);
    }

    private function runMatcher(array $config, string $commitMessage, string $rule, string $ruleName): void
    {
        $regex = new Regex($rule);

        if ((bool) $config['case_insensitive']) {
            $regex->addPatternModifier('i');
        }

        if ((bool) $config['multiline']) {
            $regex->addPatternModifier('m');
        }

        $additionalModifiersArray = array_filter(str_split((string) $config['additional_modifiers']));
        array_map([$regex, 'addPatternModifier'], $additionalModifiersArray);

        if (!preg_match((string) $regex, $commitMessage)) {
            throw new RuntimeException("Rule not matched: \"$ruleName\" $rule");
        }
    }

    private function getSpecialPrefixLength(string $string): int
    {
        if (1 !== preg_match('/^(fixup|squash)! /', $string, $match)) {
            return 0;
        }

        return mb_strlen($match[0]);
    }

    private function subjectHasPunctuations(GitCommitMsgContext $context): bool
    {
        $subjectLine = $this->getSubjectLine($context);

        if (trim($subjectLine) === '') {
            return false;
        }

        return Str::containsOneOf($subjectLine, ['.', '!', '?', ',']);
    }

    private function subjectHasTrailingPeriod(GitCommitMsgContext $context): bool
    {
        $subjectLine = $this->getSubjectLine($context);

        if ('' === trim($subjectLine)) {
            return false;
        }

        if ('.' !== mb_substr(rtrim($subjectLine), -1)) {
            return false;
        }

        return true;
    }

    private function subjectIsCapitalized(GitCommitMsgContext $context): bool
    {
        $commitMessage = $context->getCommitMessage();

        if ('' === trim($commitMessage)) {
            return true;
        }

        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);
        $subject = array_reduce($lines, function (?string $subject, string $line) {
            if (null !== $subject) {
                return $subject;
            }

            if ('' === trim($line)) {
                return null;
            }

            return $line;
        }, null);

        if (null === $subject || 1 !== preg_match('/^[[:punct:]]*(.)/u', $subject, $match)) {
            return false;
        }

        $firstLetter = (string) ($match[1] ?? '');

        return !(1 !== preg_match('/^(fixup|squash)!/u', $subject) && 1 !== preg_match('/[[:upper:]]/u', $firstLetter));
    }

    private function subjectIsSingleLined(GitCommitMsgContext $context): bool
    {
        $commitMessage = $context->getCommitMessage();

        if ('' === trim($commitMessage)) {
            return true;
        }

        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);

        return !(array_key_exists(1, $lines) && '' !== trim($lines[1]));
    }

    private function getCommitMessageLinesWithoutComments(string $commitMessage): array
    {
        $commentChar = trim($this->repository->tryToRunWithFallback(
            function (): ?string {
                return $this->repository->run('config', ['--get', 'core.commentChar']);
            },
            '#'
        ));

        $lines = preg_split('/\R/u', $commitMessage);
        $everythingBelowWillBeIgnored = false;

        return array_values(array_filter($lines, function ($line) use (&$everythingBelowWillBeIgnored, $commentChar) {
            if (mb_stripos($line, $commentChar.' Everything below it will be ignored.') !== false) {
                $everythingBelowWillBeIgnored = true;
                return false;
            }
            return 0 !== strpos($line, $commentChar) && !$everythingBelowWillBeIgnored;
        }));
    }

    private function enforceTypeScopeConventions(): bool
    {
        $config = $this->getConfig()->getOptions();

        $conventionsKeys = array_keys($config['type_scope_conventions']);

        return in_array('types', $conventionsKeys) || in_array('scopes', $conventionsKeys);
    }

    /**
     * @throws RuntimeException
     */
    private function checkTypeScopeConventions(GitCommitMsgContext $context): void
    {
        $config = $this->getConfig()->getOptions();
        $subjectLine = $this->getSubjectLine($context);

        $types = isset($config['type_scope_conventions']['types'])
            ? $config['type_scope_conventions']['types']
            : [];

        $scopes = isset($config['type_scope_conventions']['scopes'])
            ? $config['type_scope_conventions']['scopes']
            : [];

        $specialPrefix = '(?:(?:fixup|squash)! )?';
        $typesPattern = '([a-zA-Z0-9]+)';
        $scopesPattern = '(:\s|(\(.+\)?:\s))';
        $subjectPattern = '([a-zA-Z0-9-_ #@\'\/\\"]+)';
        $mergePattern = '(Merge branch \'.+\'\s.+|Merge remote-tracking branch \'.+\'|Merge pull request #\d+\s.+)';

        if (count($types) > 0) {
            $types = implode('|', $types);
            $typesPattern = '(' . $types . ')';
        }

        if (count($scopes) > 0) {
            $scopes = implode('|', $scopes);
            $scopesPattern = '(:\s|(\((?:' . $scopes . ')\)?:\s))';
        }

        $rule = '/^' . $specialPrefix . $typesPattern . $scopesPattern . $subjectPattern . '|' . $mergePattern . '/';

        try {
            $this->runMatcher($config, $subjectLine, $rule, 'Invalid Type/Scope Format');
        } catch (RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Gets a clean subject line from the commit message
     *
     * @param $context
     * @return string
     */
    private function getSubjectLine(GitCommitMsgContext $context)
    {
        $commitMessage = $context->getCommitMessage();
        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);
        return (string) $lines[0];
    }
}
