<?php

namespace spec\GrumPHP\Git;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\Repository;
use Gitonomy\Git\WorkingCopy;
use GrumPHP\Locator\GitRepositoryLocator;
use PhpSpec\ObjectBehavior;
use GrumPHP\Git\GitRepository;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

class GitRepositorySpec extends ObjectBehavior
{
    public function let(GitRepositoryLocator $locator, Repository $repository): void
    {
        $this->beConstructedWith($locator, []);
        $locator->locate([])->willReturn($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GitRepository::class);
    }

    public function it_is_lazy(GitRepositoryLocator $locator, Repository $repository): void
    {
        $repository->run(Argument::cetera())->willReturn('ok');
        $this->run('command', []);
        $this->run('command', []);

        $locator->locate([])->shouldBeCalledTimes(1);
    }

    public function it_can_run_commands(Repository $repository): void
    {
        $repository->run('command', [])->willReturn('ok');
        $this->run('command', [])->shouldBe('ok');
    }

    public function it_can_deal_with_errornous_throwing_runs(Process $process): void
    {
        $this->tryToRunWithFallback(
            function () use ($process) {
                throw new ProcessException($process->getWrappedObject());
            },
            'fallback'
        )->shouldBe('fallback');
    }

    public function it_can_deal_with_errornous_null_returing_runs(): void
    {
        $this->tryToRunWithFallback(
            function () {
                return null;
            },
            'fallback'
        )->shouldBe('fallback');
    }

    public function it_can_deal_with_valid_run_results(): void
    {
        $this->tryToRunWithFallback(
            function () {
                return 'ok';
            },
            'fallback'
        )->shouldBe('ok');
    }

    public function it_can_fetch_working_copy(Repository $repository, WorkingCopy $workingCopy): void
    {
        $repository->getWorkingCopy()->willReturn($workingCopy);
        $this->getWorkingCopy()->shouldBe($workingCopy);
    }

    public function it_can_parse_raw_diff(Repository $repository): void
    {
        $rawDiff = 'diff --git a/file.txt b/file.txt
new file mode 100644
index 0000000000000000000000000000000000000000..9766475a4185a151dc9d56d614ffb9aaea3bfd42
--- /dev/null
+++ b/file.txt
@@ -0,0 +1 @@
+content
';
        $diff = $this->createRawDiff($rawDiff);
        $diff->shouldBeAnInstanceOf(Diff::class);
        /** @var File $file */
        $file = $diff->getFiles()[0];
        $file->isCreation()->shouldBe(true);
        $file->getName()->shouldBe('file.txt');
        $file->getRepository()->shouldBe($repository);

    }
}
