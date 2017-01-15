<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\LinterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractLinterTask implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var LinterInterface
     */
    protected $linter;

    /**
     * @param GrumPHP         $grumPHP
     * @param LinterInterface $linter
     */
    public function __construct(GrumPHP $grumPHP, LinterInterface $linter)
    {
        $this->grumPHP = $grumPHP;
        $this->linter = $linter;
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'ignore_patterns' => [],
        ]);

        $resolver->addAllowedTypes('ignore_patterns', ['array']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * Validates if the linter is installed.
     *
     * @throws RuntimeException
     */
    protected function guardLinterIsInstalled()
    {
        if (!$this->linter->isInstalled()) {
            throw new RuntimeException(
                sprintf('The %s can\'t run on your system. Please install all dependencies.', $this->getName())
            );
        }
    }

    /**
     * @param FilesCollection $files
     *
     * @return LintErrorsCollection
     */
    protected function lint(FilesCollection $files)
    {
        $this->guardLinterIsInstalled();

        // Skip ignored patterns:
        $configuration = $this->getConfiguration();
        foreach ($configuration['ignore_patterns'] as $pattern) {
            $files = $files->notPath($pattern);
        }

        // Lint every file:
        $lintErrors = new LintErrorsCollection();
        foreach ($files as $file) {
            foreach ($this->linter->lint($file) as $error) {
                $lintErrors->add($error);
            }
        }

        return $lintErrors;
    }
}
