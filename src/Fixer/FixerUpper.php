<?php

declare(strict_types=1);

namespace GrumPHP\Fixer;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Configuration\Model\FixerConfig;
use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Runner\TaskResultInterface;
use Symfony\Component\Console\Exception\RuntimeException;

class FixerUpper
{
    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * @var FixerConfig
     */
    private $config;

    public function __construct(IOInterface $IO, FixerConfig $config)
    {
        $this->IO = $IO;
        $this->config = $config;
    }

    public function fix(TaskResultCollection $results): void
    {
        $fixable = $results->filter(
            static function (TaskResultInterface $result): bool {
                return $result instanceof FixableTaskResult;
            }
        );

        if (!$this->shouldRun($fixable)) {
            return;
        }

        $this->IO->write($this->IO->colorize(['Auto-Fixing tasks...'], 'yellow'));
        $info = 'Running fixer %s/%s: %s... ';
        $total = $fixable->count();
        $count = 1;

        /** @var FixableTaskResult $item */
        foreach ($fixable as $index => $item) {
            $config = $item->getTask()->getConfig();
            $label = $config->getMetadata()->label() ?: $config->getName();

            $this->IO->write([sprintf($info, $count, $total, $label)], false);
            $result = $item->fix();
            $this->IO->write(
                $result->ok() ? $this->IO->colorize(['✔'], 'green') : $this->IO->colorize(['✘'], 'red')
            );

            if ($this->IO->isVerbose() && $result->error()) {
                $this->IO->writeError($this->IO->colorize([$result->error()->getMessage()], 'red'));
            }

            $count++;
        }

        $this->IO->style()->warning('Please review the code changes that I made!');
    }

    /**
     * @param TaskResultCollection $fixable
     */
    private function shouldRun(TaskResultCollection $fixable): bool
    {
        if (!$fixable->count()) {
            return false;
        }

        if (!$this->config->isEnabled()) {
            return false;
        }

        try {
            $shouldFix = $this->IO->style()->confirm(
                'I can fix some stuff automatically, do you want me to?',
                $this->config->fixByDefault()
            );
        } catch (RuntimeException $askException) {
            return $this->config->fixByDefault();
        }

        return $shouldFix;
    }
}
