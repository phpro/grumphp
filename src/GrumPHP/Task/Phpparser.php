<?php
namespace GrumPHP\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Runner\TaskResult;

/**
 * Php Parser task
 */
class Phpparser extends AbstractParserTask
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
        $resolver->setDefaults(array(
            'triggered_by'     => array('php'),
        ));

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
            return TaskResult::createSkipped($this, $context);
        }

        $this->parser->setParserOptions(['kind' => \GrumPHP\Parser\Php\PhpParser::KIND_PHP7]);

        $parseErrors = $this->parse($files);

        if ($parseErrors->count()) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf(
                    "Some errors occured while parsing your PHP files:\n%s",
                    $parseErrors->__toString()
                )
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
