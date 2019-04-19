<?php
namespace GrumPHPE2E;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidatePathsTask implements TaskInterface
{
    /**
     * @var array|string[]
     */
    private $availableFiles;

    public function __construct(array $availableFiles)
    {
        $this->availableFiles = $availableFiles;
    }

    public function getName(): string
    {
        return 'validatePaths';
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        return new OptionsResolver();
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return true;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $contextFiles = $context->getFiles()->map(function(\SplFileInfo $file) {
            return $file->getPathname();
        })->toArray();


        try {
            Assert::assertEquals($this->availableFiles, $contextFiles);
        } catch (ExpectationFailedException $exception) {
            $message = $exception->getComparisonFailure()
                ? $exception->getComparisonFailure()->toString()
                : $exception->toString();
        }

        if ($message !== null) {
            return TaskResult::createFailed($this, $context, 'Unexpected files: '.$message);
        }

        return TaskResult::createPassed($this, $context);
    }
}
