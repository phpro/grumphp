<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use InvalidArgumentException;
use SimpleXMLElement;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $resolver->setDefaults([
            'level' => 100,
        ]);

        $resolver->addAllowedTypes('clover_file', ['string']);
        $resolver->addAllowedTypes('level', ['int', 'float']);

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

    public function run(ContextInterface $context)
    {
        $percentage = round(min(100, max(0, (float) $this->getConfiguration()['level'])), 2);

        if (!file_exists($this->getConfiguration()['clover_file'])) {
            return TaskResult::createFailed($this, $context, 'Invalid input file provided');
        }

        if (!$percentage) {
            return TaskResult::createFailed(
                $this,
                $context,
                'An integer checked percentage must be given as second parameter'
            );
        }

        $xml             = new SimpleXMLElement(file_get_contents($this->getConfiguration()['clover_file']));
        $metrics         = $xml->xpath('//metrics');
        $totalElements   = 0;
        $checkedElements = 0;

        foreach ($metrics as $metric) {
            $totalElements   += (int) $metric['elements'];
            $checkedElements += (int) $metric['coveredelements'];
        }

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
