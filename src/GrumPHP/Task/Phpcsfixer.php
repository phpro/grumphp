<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\RuntimeException;

/**
 * Php-cs-fixer task
 */
class Phpcsfixer extends AbstractExternalTask
{
    const COMMAND_NAME = 'php-cs-fixer';

    /**
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'auto_fix' => false,
            'config' => 'default',
            'fixers' => array(),
            'level' => '',
            'verbose' => true,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandLocation()
    {
        return $this->externalCommandLocator->locate(self::COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function run(FilesCollection $files)
    {
        $files = $files->name('*.php');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();

        $this->processBuilder->setArguments(array(
            $this->getCommandLocation(),
            '--format=json',
        ));

        if ($config['level']) {
            $this->processBuilder->add('--level=' . $config['level']);
        }

        if ($config['config']) {
            $this->processBuilder->add('--config=' . $config['config']);
        }

        if (!$config['auto_fix']) {
            $this->processBuilder->add('--dry-run');
        }

        if ($config['verbose']) {
            $this->processBuilder->add('--verbose');
        }

        if (count($config['fixers'])) {
            $this->processBuilder->add('--fixers=' . implode(',', $config['fixers']));
        }

        $this->processBuilder->add('fix');

        $messages = array();
        $suggest = array('You can fix all errors by running following commands:');
        $errorCount = 0;
        foreach ($files as $file) {
            $processBuilder = clone $this->processBuilder;
            $processBuilder->add($file);
            $process = $processBuilder->getProcess();
            $process->run();
            if (!$process->isSuccessful()) {
                $output = $process->getOutput();
                $json = json_decode($output, true);
                if ($json) {
                    if (isset($json['files'][0]['name']) && isset($json['files'][0]['appliedFixers'])) {
                        $messages[] = sprintf(
                            '%s) %s (%s)',
                            ++$errorCount,
                            $json['files'][0]['name'],
                            implode(',', $json['files'][0]['appliedFixers'])
                        );
                    } elseif (isset($json['files'][0]['name'])) {
                        $messages[] = sprintf(
                            '%s) %s',
                            ++$errorCount,
                            $json['files'][0]['name']
                        );
                    }

                    $suggest[] = str_replace(array("'--dry-run' ", "'--format=json' "), '', $process->getCommandLine());
                } else {
                    $messages[] = $output;
                }

            }
        }

        if (count($messages) && !$config['auto_fix']) {
            throw new RuntimeException(implode("\n", $messages) . "\n" . "\n" . implode("\n", $suggest));
        }
    }
}
