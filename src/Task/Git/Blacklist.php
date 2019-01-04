<?php

declare(strict_types=1);

namespace GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\IO\IOInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Git Blacklist Task.
 */
class Blacklist extends AbstractExternalTask
{
    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * Blacklist constructor.
     */
    public function __construct(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        ProcessFormatterInterface $formatter,
        IOInterface $IO
    ) {
        $this->IO = $IO;
        parent::__construct($grumPHP, $processBuilder, $formatter);
    }

    public function getName(): string
    {
        return 'git_blacklist';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'keywords' => [],
            'whitelist_patterns' => [],
            'triggered_by' => ['php'],
            'regexp_type' => 'G',
        ]);

        $resolver->addAllowedTypes('keywords', ['array']);
        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('regexp_type', ['string']);

        $resolver->setAllowedValues('regexp_type', ['G', 'E', 'P']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        $whitelistPatterns = $config['whitelist_patterns'];
        $extensions = $config['triggered_by'];

        $files = $context->getFiles();
        if (0 !== \count($whitelistPatterns)) {
            $files = $files->paths($whitelistPatterns);
        }
        $files = $files->extensions($extensions);

        if (0 === \count($files) || empty($config['keywords'])) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('git');
        $arguments->add('grep');
        $arguments->add('--cached');
        $arguments->add('-n');
        $arguments->add('--break');
        $arguments->add('--heading');
        $arguments->addOptionalArgument('--color', $this->IO->isDecorated());
        $arguments->addOptionalArgument('-%s', $config['regexp_type']);
        $arguments->addArgumentArrayWithSeparatedValue('-e', $config['keywords']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if ($process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, sprintf(
                'You have blacklisted keywords in your commit:%s%s',
                PHP_EOL,
                $this->formatter->format($process)
            ));
        }

        return TaskResult::createPassed($this, $context);
    }
}
