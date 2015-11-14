<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\Process\ProcessBuilder;

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
            'config' => 'default',
            'config_file' => null,
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
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();

        $arguments = ProcessArgumentsCollection::forExecutable($this->getCommandLocation());
        $arguments->add('--format=json');
        $arguments->add('--dry-run');
        $arguments->addOptionalArgument('--level=%s', $config['level']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--config-file=%s', $config['config_file']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->addOptionalCommaSeparatedArgument('--fixers=%s', $config['fixers']);
        $arguments->add('fix');

        $this->processBuilder->setArguments($arguments->getValues());

        $messages = array();
        $suggest = array('You can fix all errors by running following commands:');
        $errorCount = 0;
        foreach ($files as $file) {
            $processBuilder = clone $this->processBuilder;
            $processBuilder->setArguments($arguments->getValues());
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

        if (count($messages)) {
            throw new RuntimeException(implode("\n", $messages) . "\n" . "\n" . implode("\n", $suggest));
        }
    }
}
