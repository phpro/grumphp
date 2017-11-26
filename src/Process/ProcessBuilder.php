<?php declare(strict_types=1);

namespace GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\PlatformException;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Util\Platform;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder as SymfonyProcessBuilder;

class ProcessBuilder
{
    /**
     * @var ExternalCommand
     */
    private $externalCommandLocator;

    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * ProcessBuilder constructor.
     */
    public function __construct(GrumPHP $config, ExternalCommand $externalCommandLocator, IOInterface $io)
    {
        $this->externalCommandLocator = $externalCommandLocator;
        $this->config = $config;
        $this->io = $io;
    }

    /**
     * @return ProcessArgumentsCollection
     */
    public function createArgumentsForCommand(string $command): ProcessArgumentsCollection
    {
        $executable = $this->getCommandLocation($command);

        return ProcessArgumentsCollection::forExecutable($executable);
    }

    /**
     * @return Process
     * @throws \GrumPHP\Exception\PlatformException
     */
    public function buildProcess(ProcessArgumentsCollection $arguments): Process
    {
        $builder = SymfonyProcessBuilder::create($arguments->getValues());
        $builder->setTimeout($this->config->getProcessTimeout());
        $process = $builder->getProcess();

        $this->logProcessInVerboseMode($process);
        $this->guardWindowsCmdMaxInputStringLimitation($process);

        return $process;
    }

    /**
     * @throws \GrumPHP\Exception\PlatformException
     */
    private function guardWindowsCmdMaxInputStringLimitation(Process $process)
    {
        if (!Platform::isWindows()) {
            return;
        }

        if (strlen($process->getCommandLine()) <= Platform::WINDOWS_COMMANDLINE_STRING_LIMITATION) {
            return;
        }

        throw PlatformException::commandLineStringLimit($process);
    }

    private function getCommandLocation(string $command): string
    {
        return $this->externalCommandLocator->locate($command);
    }

    private function logProcessInVerboseMode(Process $process)
    {
        if ($this->io->isVeryVerbose()) {
            $this->io->write(PHP_EOL . 'Command: ' . $process->getCommandLine(), true);
        }
    }
}
