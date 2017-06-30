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
 * Git TextWidth Task
 */
class TextWidth implements TaskInterface
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
        return 'git_text_width';
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
            'max_body_width' => 72,
            'max_subject_width' => 60,
        ]);

        $resolver->addAllowedTypes('max_body_width', ['int']);
        $resolver->addAllowedTypes('max_subject_width', ['int']);

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
     */
    public function run(ContextInterface $context)
    {
        $commitMessage = $context->getCommitMessage();

        if (trim($commitMessage) === '') {
            return TaskResult::createPassed($this, $context);
        }

        $errors = [];
        $commitMessage = str_replace("\r", '', $commitMessage);
        $lines = explode("\n", $commitMessage);
        $config = $this->getConfiguration();
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

        if (count($errors) === 0) {
            return TaskResult::createPassed($this, $context);
        }

        return TaskResult::createFailed($this, $context, implode(PHP_EOL, $errors));
    }

    private function getSpecialPrefixLength($string)
    {
        if (preg_match('/^(fixup|squash)! /', $string, $match) !== 1) {
            return 0;
        }

        return mb_strlen($match[0]);
    }
}
