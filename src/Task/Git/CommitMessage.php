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
 * Git CommitMessage Task
 */
class CommitMessage implements TaskInterface
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
        return 'git_commit_message';
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
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => true,
            'enforce_single_lined_subject' => true,
            'max_body_width' => 72,
            'max_subject_width' => 60,
            'case_insensitive' => true,
            'multiline' => true,
            'matchers' => [],
            'additional_modifiers' => ''
        ]);

        $resolver->addAllowedTypes('allow_empty_message', ['bool']);
        $resolver->addAllowedTypes('enforce_capitalized_subject', ['bool']);
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
     *
     * @return TaskResult
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();
        $commitMessage = $context->getCommitMessage();
        $exceptions = [];

        if (!(bool) $config['allow_empty_message'] && trim($commitMessage) === '') {
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

        if ((bool) $config['enforce_no_subject_trailing_period'] && $this->subjectHasTrailingPeriod($context)) {
            return TaskResult::createFailed(
                $this,
                $context,
                'Please omit trailing period from commit message subject.'
            );
        }

        foreach ($config['matchers'] as $ruleName => $rule) {
            try {
                $this->runMatcher($config, $commitMessage, $rule, $ruleName);
            } catch (RuntimeException $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (count($exceptions)) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $exceptions));
        }

        return $this->enforceTextWidth($context);
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResult
     */
    private function enforceTextWidth(ContextInterface $context)
    {
        $commitMessage = $context->getCommitMessage();
        $config = $this->getConfiguration();

        if (trim($commitMessage) === '') {
            return TaskResult::createPassed($this, $context);
        }

        $errors = [];
        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);

        $subject = rtrim($lines[0]);
        $maxSubjectWidth = $config['max_subject_width'] + $this->getSpecialPrefixLength($subject);

        if (mb_strlen($subject) > $maxSubjectWidth) {
            $errors[] = sprintf('Please keep the subject <= %u characters.', $maxSubjectWidth);
        }

        foreach (array_slice($lines, 2) as $index => $line) {
            if (mb_strlen(rtrim($line)) > $config['max_body_width']) {
                $errors[] = sprintf(
                    'Line %u of commit message has > %u characters.',
                    $index + 3,
                    $config['max_body_width']
                );
            }
        }

        if (count($errors) > 0) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $errors));
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param array $config
     * @param string $commitMessage
     * @param string $rule
     * @param string $ruleName
     *
     * @throws RuntimeException
     */
    private function runMatcher(array $config, $commitMessage, $rule, $ruleName)
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

    /**
     * @param string $string
     *
     * @return int
     */
    private function getSpecialPrefixLength($string)
    {
        if (preg_match('/^(fixup|squash)! /', $string, $match) !== 1) {
            return 0;
        }

        return mb_strlen($match[0]);
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    private function subjectHasTrailingPeriod(ContextInterface $context)
    {
        $commitMessage = $context->getCommitMessage();

        if (trim($commitMessage) === '') {
            return false;
        }

        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);

        if (mb_substr(rtrim($lines[0]), -1) !== '.') {
            return false;
        }

        return true;
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    private function subjectIsCapitalized(ContextInterface $context)
    {
        $commitMessage = $context->getCommitMessage();

        if (trim($commitMessage) === '') {
            return true;
        }

        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);
        $subject = array_reduce($lines, function ($subject, $line) {
            if ($subject !== null) {
                return $subject;
            }

            if (trim($line) === '') {
                return null;
            }

            return $line;
        }, null);


        if ($subject === null || preg_match('/^[[:punct:]]*(.)/u', $subject, $match) !== 1) {
            return false;
        }

        $firstLetter = $match[1];

        if (preg_match('/^(fixup|squash)!/u', $subject) !== 1 && preg_match('/[[:upper:]]/u', $firstLetter) !== 1) {
            return false;
        }

        return true;
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    private function subjectIsSingleLined(ContextInterface $context)
    {
        $commitMessage = $context->getCommitMessage();

        if (trim($commitMessage) === '') {
            return true;
        }

        $lines = $this->getCommitMessageLinesWithoutComments($commitMessage);

        if (array_key_exists(1, $lines) && trim($lines[1]) !== '') {
            return false;
        }

        return true;
    }

    /**
     * @param string $commitMessage
     *
     * @return array
     */
    private function getCommitMessageLinesWithoutComments($commitMessage)
    {
        $lines = preg_split('/\R/u', $commitMessage);

        return array_values(array_filter($lines, function ($line) {
            return strpos($line, '#') !== 0;
        }));
    }
}
