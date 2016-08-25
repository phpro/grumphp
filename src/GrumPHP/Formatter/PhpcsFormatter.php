<?php

namespace GrumPHP\Formatter;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class PhpcsFormatter
 *
 * @package GrumPHP\Formatter
 */
class PhpcsFormatter implements ProcessFormatterInterface
{
    /**
     * @var string
     */
    protected $output = '';

    /**
     * @var string[]
     */
    protected $suggestedFiles = array();

    /**
     * @param Process $process
     *
     * @return string
     */
    public function format(Process $process)
    {
        $output = $process->getOutput();
        if (!$output) {
            return $process->getErrorOutput();
        }

        $pos = strrpos($output, "\n");
        if ($pos === false) {
            return $output;
        } else {
            $lastLine = substr($output, $pos + 1);
        }
        if (!$json = json_decode($lastLine, true)) {
            return $output;
        }

        $this->output = trim(substr($output, 0, $pos));
        $this->suggestedFiles = $this->getSuggestedFilesFromJson($json);

        if (empty($this->suggestedFiles)) {
            return $this->output;
        }

        return '';
    }

    /**
     * @param array $json
     * @return string[]
     */
    public function getSuggestedFilesFromJson(array $json)
    {
        $suggestedFiles = array();
        if (!isset($json['totals']) || $json['totals']['fixable'] == 0) {
            return $suggestedFiles;
        }
        foreach ($json['files'] as $absolutePath => $data) {
            if (!is_array($data) || empty($data['messages'])) {
                continue;
            }
            foreach ($data['messages'] as $message) {
                if (is_array($message) && $message['fixable']) {
                    $suggestedFiles[] = $absolutePath;
                    break;
                }
            }
        }
        return $suggestedFiles;
    }

    /**
     * @param ProcessArgumentsCollection $defaultArguments
     * @param ProcessBuilder $processBuilder
     * @return string
     */
    public function formatErrorMessage(ProcessArgumentsCollection $defaultArguments, ProcessBuilder $processBuilder)
    {
        $defaultArguments->addArgumentArray('%s', $this->suggestedFiles);
        return sprintf(
            '%sYou can fix some errors by running following command:%s',
            $this->output . PHP_EOL . PHP_EOL,
            PHP_EOL . $processBuilder->buildProcess($defaultArguments)->getCommandLine()
        );
    }
}
