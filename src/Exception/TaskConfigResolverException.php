<?php

declare(strict_types=1);

namespace GrumPHP\Exception;

use GrumPHP\Task\TaskInterface;

class TaskConfigResolverException extends RuntimeException
{
    public static function unknownTask(string $task): self
    {
        return new self('Could not load config resolver for task: "'.$task.'". The task is not known.');
    }

    public static function unknownClass(string $class): self
    {
        return new self(
            sprintf(
                'Could not load config resolver for class: "%s". Expected an instance of: "%s"',
                $class,
                TaskInterface::class
            )
        );
    }
}
