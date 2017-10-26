<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileSize implements TaskInterface
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
     * @return string
     */
    public function getName()
    {
        return 'file_size';
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
            'max_size' => '10M',
        ]);

        $resolver->addAllowedTypes('max_size', ['string', 'integer']);

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
     * @param ContextInterface|RunContext $context
     *
     * @return TaskResult
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();

        if (0 === count($context->getFiles())) {
            return TaskResult::createSkipped($this, $context);
        }

        $maxSize = $config['max_size'];
        $files = $context->getFiles()->size(sprintf('>%s', $maxSize));

        if ($files->count() > 0) {
            $errorMessage = 'Large files detected:' . PHP_EOL;

            foreach ($files as $file) {
                $errorMessage .= sprintf(
                    '- %s exceeded the maximum size of %s.' . PHP_EOL,
                    $file->getFilename(),
                    $maxSize
                );
            }

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }
}
