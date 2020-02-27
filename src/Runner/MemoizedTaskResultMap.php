<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

final class MemoizedTaskResultMap
{
    /**
     * @var array<string, TaskResultInterface>
     */
    private $resultMap = [];

    public function onResult(TaskResultInterface $result): void
    {
        $this->resultMap[$result->getTask()->getConfig()->getName()] = $result;
    }

    public function contains(string $taskName): bool
    {
        return array_key_exists($taskName, $this->resultMap);
    }

    public function get(string $taskName): ?TaskResultInterface
    {
        return $this->resultMap[$taskName] ?? null;
    }
}
