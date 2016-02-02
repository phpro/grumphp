<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Util\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'case_insensitive' => true,
            'multiline' => true,
            'matchers' => array(),
            'additional_modifiers' => ''
        ));

        $resolver->addAllowedTypes('case_insensitive', array('bool'));
        $resolver->addAllowedTypes('multiline', array('bool'));
        $resolver->addAllowedTypes('matchers', array('array'));
        $resolver->addAllowedTypes('additional_modifiers', array('string'));

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

            $additionalModifiersArray = array_filter(str_split((string) $config['additional_modifiers']));
            array_map(array($regex, 'addPatternModifier'), $additionalModifiersArray);

            if (!preg_match($regex->__toString(), $commitMessage)) {
                throw new RuntimeException(
                    sprintf('The commit message does not match the rule: %s', $rule)
                );
            }
        }
    }
}
