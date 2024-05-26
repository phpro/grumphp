<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\GitPrePushContext;

trait GitContextTrait
{
    protected function isGitContextAllowed(ContextInterface $context): bool
    {
        \assert($this instanceof TaskInterface);
        $gitStage = match (true) {
            $context instanceof GitPreCommitContext => 'pre-commit',
            $context instanceof GitPrePushContext => 'pre-push',
            default => null,
        };
        return in_array($gitStage, $this->getConfig()->getMetadata()->gitStages(), true);
    }
}
