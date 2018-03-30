<?php

namespace spec\GrumPHP\Task;

use ArrayIterator;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\FileSize;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileSizeSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $grumPHP->getTaskConfiguration('file_size')->willReturn([]);
        $this->beConstructedWith($grumPHP);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileSize::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('file_size');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('max_size');
        $options->getDefinedOptions()->shouldContain('ignore_patterns');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_does_not_do_anything_if_there_are_no_files(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->buildProcess('file_size')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(RunContext $context)
    {
        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('src/Collection/TaskResultCollection.php', 'src/Collection', 'TaskResultCollection.php'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
        $result->getResultCode()->shouldBe(TaskResult::PASSED);
    }

    function it_throws_exception_if_the_process_fails(RunContext $context, FilesCollection $filesCollection, ArrayIterator $arrayIterator)
    {
        $filesCollection->count()->willReturn(1);
        $filesCollection->ignoreSymlinks()->willReturn($filesCollection);
        $filesCollection->notPaths([])->willReturn($filesCollection);
        $filesCollection->size('>10M')->willReturn(new FilesCollection([
            new SplFileInfo('src/Collection/TaskResultCollection.php', 'src/Collection', 'TaskResultCollection.php'),
        ]));
        $filesCollection->getIterator()->willReturn($arrayIterator);

        $context->getFiles()->willReturn($filesCollection);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
        $result->getResultCode()->shouldBe(TaskResult::FAILED);
        $result->getMessage()->shouldBe('Large files detected:' . PHP_EOL . '- TaskResultCollection.php exceeded the maximum size of 10M.' . PHP_EOL);
    }
}
