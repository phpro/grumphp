<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRegex implements TaskInterface
{

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @param GrumPHP $grumPHP
     */
    public function __construct(GrumPHP $grumPHP)
    {
        $this->grumPHP = $grumPHP;
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
          'case_insensitive' => true,
          'matchers' => [],
          'additional_modifiers' => ''
        ]);

        $resolver->addAllowedTypes('case_insensitive', ['bool']);
        $resolver->addAllowedTypes('matchers', ['array']);
        $resolver->addAllowedTypes('additional_modifiers', ['string']);

        return $resolver;
    }
}
