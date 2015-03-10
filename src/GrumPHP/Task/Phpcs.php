<?php

namespace GrumPHP\Task;

/**
 * Class Phpcs
 *
 * @package GrumPHP\Task
 */
class Phpcs extends AbstractExternalTask
{

    const COMMAND_NAME = 'phpcs';

    /**
     * @return string
     */
    public function getCommandLocation()
    {
        return $this->externalCommandLocator->locate(self::COMMAND_NAME);
    }

    /**
     * @param array $files
     */
    public function run(array $files)
    {
        $phpcsConfig = $this->grumPHP->getConfiguration('phpcs'); // TODO: task should have access to config
        foreach ($files as $file) {
            $suffix = substr($file, strlen($file) - 8);

            if ('Spec.php' === $suffix || 'Test.php' === $suffix) {
                continue;
            }

            $this->processBuilder->setArguments(array(
                'php',
                $this->getCommandLocation(),
                '--standard=' . $phpcsConfig->getStandard(),
                $file,
            ));

            $process = $this->processBuilder->getProcess();
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getOutput());
            }
        }
    }

}
