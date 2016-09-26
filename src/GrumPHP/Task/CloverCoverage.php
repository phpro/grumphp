<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use SimpleXMLElement;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Clover unit test coverage task
 */
class CloverCoverage implements TaskInterface
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
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'clover_coverage';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();

        $resolver->setDefined('clover_file');
        $resolver->setDefined('level');

        $resolver->addAllowedTypes('clover_file', array('string'));
        $resolver->addAllowedTypes('level', array('int', 'float'));

        $resolver->setDefaults(array(
            'level' => 100,
        ));

        $resolver->setRequired('clover_file');

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
        $configuration = $this->getConfiguration();
        $percentage = round(min(100, max(0, (float) $configuration['level'])), 2);
        $cloverFile = $configuration['clover_file'];

        if (!$this->grumPHP->getFilesystem()->exists($cloverFile)) {
            return TaskResult::createFailed($this, $context, 'Invalid input file provided');
        }

        if (!$percentage) {
            return TaskResult::createFailed(
                $this,
                $context,
                'An integer checked percentage must be given as second parameter'
            );
        }

        $xml             = new SimpleXMLElement(file_get_contents($cloverFile));
        $totalElements   = (string)current($xml->xpath('/coverage/project/metrics/@elements'));
        $checkedElements = (string)current($xml->xpath('/coverage/project/metrics/@coveredelements'));

        $coverage = round(($checkedElements / $totalElements) * 100, 2);

        if ($coverage < $percentage) {
            $message = sprintf(
                'Code coverage is %1$d%%, which is below the accepted %2$d%%' . PHP_EOL,
                $coverage,
                $percentage
            );
            return TaskResult::createFailed($this, $context, $message);
        }

        return TaskResult::createPassed($this, $context);
    }
}
