<?php
namespace GrumPHP\Task;

use GrumPHP\Task\AbstractParserTask;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

/**
 * Php Parser task
 */
class Parser extends AbstractParserTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'php_parser';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableOptions()
    {
        $resolver = parent::getConfigurableOptions();
        $resolver->setDefault('triggered_by', array('php'));

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();

        $files = $context->getFiles(false)->extensions($config['triggered_by']);
        if (0 === count($files)) {
            return;
        }
        $parseErrors = $this->parse($files);

        if ($parseErrors->count()) {
            throw new RuntimeException(sprintf(
                "You have blacklisted keywords in your commit:\n%s",
                $parseErrors->__toString()
            ));
        }
    }
}
