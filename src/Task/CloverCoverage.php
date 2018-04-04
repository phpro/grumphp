<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Filesystem;
use SimpleXMLElement;
use SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Clover unit test coverage task.
 */
class CloverCoverage implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(GrumPHP $grumPHP, Filesystem $filesystem)
    {
        $this->grumPHP = $grumPHP;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    public function getName(): string
    {
        return 'clover_coverage';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();

        $resolver->setDefined('clover_file');
        $resolver->setDefined('level');

        $resolver->addAllowedTypes('clover_file', ['string']);
        $resolver->addAllowedTypes('level', ['int', 'float']);

        $resolver->setDefaults([
            'level' => 100,
        ]);

        $resolver->setRequired('clover_file');

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $configuration = $this->getConfiguration();
        $percentage = round(min(100, max(0, (float) $configuration['level'])), 2);
        $cloverFile = $configuration['clover_file'];

        if (!$this->filesystem->exists($cloverFile)) {
            return TaskResult::createFailed($this, $context, 'Invalid input file provided');
        }

        if (!$percentage) {
            return TaskResult::createFailed(
                $this,
                $context,
                'An integer checked percentage must be given as second parameter'
            );
        }

        $xml = new SimpleXMLElement($this->filesystem->readFromFileInfo(new SplFileInfo($cloverFile)));
        $totalElements = (string) current($xml->xpath('/coverage/project/metrics/@elements'));
        $checkedElements = (string) current($xml->xpath('/coverage/project/metrics/@coveredelements'));

        if (0 === (int) $totalElements) {
            return TaskResult::createSkipped($this, $context);
        }

        $coverage = round(($checkedElements / $totalElements) * 100, 2);

        if ($coverage < $percentage) {
            $message = sprintf(
                'Code coverage is %1$d%%, which is below the accepted %2$d%%'.PHP_EOL,
                $coverage,
                $percentage
            );

            return TaskResult::createFailed($this, $context, $message);
        }

        return TaskResult::createPassed($this, $context);
    }
}
