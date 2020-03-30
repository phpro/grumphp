<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Fixer\FixerUpper;
use GrumPHP\Runner\TaskRunnerContext;

class FixCodeMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var FixerUpper
     */
    private $fixerUpper;

    public function __construct(FixerUpper $fixerUpper)
    {
        $this->fixerUpper = $fixerUpper;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        /** @var TaskResultCollection $results */
        $results = $next($context);

        $this->fixerUpper->fix($results);

        return $results;
    }
}
