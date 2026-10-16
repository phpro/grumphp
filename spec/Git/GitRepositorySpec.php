<?php

namespace spec\GrumPHP\Git;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\WorkingCopy;
use GrumPHP\Locator\GitRepositoryLocator;
use GrumPHPTest\Helpers\TemporaryGitRepository;
use PhpSpec\ObjectBehavior;
use GrumPHP\Git\GitRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class GitRepositorySpec extends ObjectBehavior
{
    /**
     * @var TemporaryGitRepository
     */
    private $gitRepository;

    public function let(GitRepositoryLocator $locator, LoggerInterface $logger): void
    {
        $this->gitRepository = TemporaryGitRepository::create();
        $this->beConstructedWith($locator, $logger);
        $locator->locate($logger)->willReturn($this->gitRepository->getRepository());
    }

    public function letGo(): void
    {
        $this->gitRepository->remove();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GitRepository::class);
    }

    public function it_is_lazy(GitRepositoryLocator $locator, LoggerInterface $logger): void
    {
        $this->run('rev-parse', ['--git-dir']);
        $this->run('rev-parse', ['--git-dir']);

        $locator->locate($logger)->shouldBeCalledTimes(1);
    }

    public function it_can_run_commands(): void
    {
        $this->run('rev-parse', ['--is-bare-repository'])->shouldBe("false\n");
    }

    public function it_can_deal_with_errornous_throwing_runs(Process $process): void
    {
        $process->getCommandLine()->willReturn('');
        $process->getErrorOutput()->willReturn('');
        $process->getExitCode()->willReturn(1);
        $process->getOutput()->willReturn('');

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

    public function it_can_fetch_working_copy(): void
    {
        $this->getWorkingCopy()->shouldBeAnInstanceOf(WorkingCopy::class);
    }

    public function it_can_parse_raw_diff(): void
    {
        $rawDiff = ":000000 100644 0000000 d95f3ad A\tfile.txt

diff --git a/file.txt b/file.txt
new file mode 100644
index 0000000000000000000000000000000000000000..d95f3ad14dee633a758d2e331151e950dd13e4ed
--- /dev/null
+++ b/file.txt
@@ -0,0 +1 @@
+content
";
        $diff = $this->createRawDiff($rawDiff);
        $diff->shouldBeAnInstanceOf(Diff::class);
        /** @var File $file */
        $file = $diff->getFiles()[0];
        $file->isCreation()->shouldBe(true);
        $file->getName()->shouldBe('file.txt');
        $file->getRepository()->shouldBe($this->gitRepository->getRepository());

    }
}
