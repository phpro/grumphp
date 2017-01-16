<?php

namespace GrumPHP\Exception;

use GrumPHP\Util\Platform;
use Symfony\Component\Process\Process;

class PlatformException extends RuntimeException
{
    /**
     * @param Process $process
     *
     * @return PlatformException
     */
    public static function commandLineStringLimit(Process $process)
    {
        return new self(sprintf(
            'The Windows maximum amount of %s input characters exceeded while running process: %s ...',
            Platform::WINDOWS_COMMANDLINE_STRING_LIMITATION,
            substr($process->getCommandLine(), 0, 75)
        ));
    }
}
