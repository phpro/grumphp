<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Filesystem;
use SimpleXMLElement;
use SplFileInfo;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VeeWee\Xml\Dom\Document;

class CloverCoverage implements TaskInterface
{
    use GitContextTrait;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TaskConfigInterface
     */
    private $config;

    public function __construct(Filesystem $filesystem)
    {
        $this->config = new EmptyTaskConfig();
        $this->filesystem = $filesystem;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();

        $resolver->setDefined('clover_file');
        $resolver->setDefined('minimum_level');
        $resolver->setDefined('target_level');

        $resolver->setRequired('clover_file');

        $resolver->addAllowedTypes('clover_file', ['string']);
        $resolver->addAllowedTypes('minimum_level', ['int', 'float']);
        $resolver->addAllowedTypes('target_level', ['int', 'float', 'null']);

        $resolver->setDefaults([
            'minimum_level' => 100,
            'target_level' => null,
        ]);

        // @deprecated : Can be removed on 3.0.0
        $resolver->setDefined('level');
        $resolver->setDeprecated(
            'level',
            'grumphp',
            '2.8.0',
            'The level has been deprecated and will be removed in 3.0.0. Use minimum_level instead.'
        );
        $resolver->addAllowedTypes('level', ['int', 'float']);
        $resolver->setDefault('minimum_level', function (Options $options): int|float {
            return (float) ($options['level'] ?? 100);
        });
        // @deprecated : end

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return $this->isGitContextAllowed($context) || $context instanceof RunContext;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $configuration = $this->getConfig()->getOptions();
        $clamp = static fn (float $value): float => round(min(100, max(0, $value)), 2);
        $minimumLevel = $clamp((float) $configuration['minimum_level']);
        $targetLevel = $configuration['target_level'] ? $clamp((float) $configuration['target_level']) : null;
        $cloverFile = $configuration['clover_file'];

        if (!$cloverFile) {
            return TaskResult::createFailed($this, $context, 'No clover file provided');
        }

        if ($minimumLevel === 0.0) {
            return TaskResult::createFailed(
                $this,
                $context,
                'You must provide a positive minimum level between 1-100 for code coverage.'
            );
        }

        try {
            [
                'totalElements' => $totalElements,
                'checkedElements' => $checkedElements
            ] = $this->parseTotals($cloverFile);
        } catch (FileNotFoundException $exception) {
            return TaskResult::createFailed($this, $context, $exception->getMessage());
        }

        if (0 === $totalElements) {
            return TaskResult::createSkipped($this, $context);
        }

        $coverage = round(($checkedElements / $totalElements) * 100, 2);

        if ($coverage < $minimumLevel) {
            $message = sprintf(
                'Code coverage is %1$d%%, which is below the accepted %2$d%%'.PHP_EOL,
                $coverage,
                $minimumLevel
            );

            return TaskResult::createFailed($this, $context, $message);
        }

        if ($targetLevel !== null && $coverage < $targetLevel) {
            $message = sprintf(
                'Code coverage is %1$d%%, which is below the target %2$d%%'.PHP_EOL,
                $coverage,
                $targetLevel
            );

            return TaskResult::createNonBlockingFailed($this, $context, $message);
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @return array{'totalElements': int, 'checkedElements': int}
     *
     * @throws FileNotFoundException
     */
    private function parseTotals(string $coverageFile): array
    {
        if (!$this->filesystem->exists($coverageFile)) {
            throw new FileNotFoundException($coverageFile);
        }

        $xml = new SimpleXMLElement($this->filesystem->readFromFileInfo(new SplFileInfo($coverageFile)));
        $totalElements = (int) current($xml->xpath('/coverage/project/metrics/@elements') ?? []);
        $checkedElements = (int) current($xml->xpath('/coverage/project/metrics/@coveredelements') ?? []);

        return [
            'totalElements' => $totalElements,
            'checkedElements' => $checkedElements,
        ];
    }
}
