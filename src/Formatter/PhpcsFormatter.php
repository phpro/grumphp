<?php

declare(strict_types=1);

namespace GrumPHP\Formatter;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Process\ProcessBuilder;
use Symfony\Component\Process\Process;

class PhpcsFormatter implements ProcessFormatterInterface
{
    protected $output = '';

    /**
     * @var string[]
     */
    protected $suggestedFiles = [];

    public function format(Process $process): string
    {
        $output = $process->getOutput();
        if (!$output) {
            return $process->getErrorOutput();
        }

        $pos = strrpos($output, "\n");
        if (false === $pos) {
            return $output;
        }
        $lastLine = substr($output, $pos + 1);

        if (!$json = json_decode($lastLine, true)) {
            return $output;
        }

        $this->output = trim(substr($output, 0, $pos));
        $this->suggestedFiles = $this->getSuggestedFilesFromJson($json);

        return $this->output;
    }

    public function getSuggestedFilesFromJson(array $json): array
    {
        $suggestedFiles = [];
        if (!isset($json['totals']['fixable']) || $json['totals']['fixable'] === 0) {
            return $suggestedFiles;
        }
        foreach ($json['files'] as $absolutePath => $data) {
            if (!\is_array($data) || empty($data['messages'])) {
                continue;
            }
            foreach ($data['messages'] as $message) {
                if (\is_array($message) && $message['fixable']) {
                    $suggestedFiles[] = $absolutePath;
                    break;
                }
            }
        }

        return $suggestedFiles;
    }

    public function formatErrorMessage(
        ProcessArgumentsCollection $defaultArguments,
        ProcessBuilder $processBuilder
    ): string {
        if (empty($this->suggestedFiles)) {
            return '';
        }
        $defaultArguments->addArgumentArray('%s', $this->suggestedFiles);

        return sprintf(
            '%sYou can fix some errors by running following command:%s',
            PHP_EOL.PHP_EOL,
            PHP_EOL.$processBuilder->buildProcess($defaultArguments)->getCommandLine()
        );
    }
}
