# Extending the TaskRunner

## Events

It is possible to hook in to GrumPHP with events.
Internally the Symfony event dispatcher is being used. 

The following events are triggered during execution:

| Event name              | Event class           | Triggered
| ----------------------- | --------------------- | ----------
| grumphp.task.run        | TaskEvent             | before a task is executed
| grumphp.task.failed     | TaskFailedEvent       | when a task fails
| grumphp.task.complete   | TaskEvent             | when a task succeeds
| grumphp.runner.run      | RunnerEvent           | before the tasks are executed
| grumphp.runner.failed   | RunnerFailedEvent     | when one task failed
| grumphp.runner.complete | RunnerEvent           | when all tasks succeed
| console.command         | ConsoleCommandEvent   | before a CLI command is ran
| console.terminate       | ConsoleTerminateEvent | before a CLI command terminates
| console.exception       | ConsoleExceptionEvent | when a CLI command throws an unhandled exception.

Configure events just like you would in Symfony:

```yml
# grumphp.yml
services:   
    listener.some_listener:
        class: MyNamespace\EventListener\MyListener
        tags:
            - { name: grumphp.event_listener, event: grumphp.runner.run }
            - { name: grumphp.event_listener, event: grumphp.runner.run, method: customMethod, priority: 10 }
    listener.some_subscriber:
        class: MyNamespace\EventSubscriber\MySubscriber
        tags:
            - { name: grumphp.event_subscriber }
```

## Register a RunnerMiddleware

A runner middleware can be used to adjust how a set of tasks is being executed.
Examples are: resorting tasks, filtering tasks, logging, ....
A runner middleware can be written by implementing the `RunnerMiddlewareInterface`.

**Example:**

```php
use GrumPHP\Runner\Middleware\RunnerMiddlewareInterface;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskRunnerContext;

class PassThroughMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @param callable(TaskRunnerContext $info): TaskResultCollection $next
     */
    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        return $next($context);    
    }
}
```

Configuration:

```yaml
# grumphp.yaml

services:
    PassThroughMiddleware:
        tags:
            - { name: 'grumphp.runner_middleware', priority: 500 }
```


## Register a TaskHandlerMiddlewareInterface

A task handler middleware can be used to adjust how to run one single task.
Examples are: event dispatching, caching, logging, ...
A runner middleware works Asynchronous in order to allow parallel execution and can be written by implementing the `TaskHandlerMiddlewareInterface`.
You need to return an [amp](https://github.com/amphp/amp) promise object.

**Example:**

```php
use GrumPHP\Runner\TaskHandler\Middleware\TaskHandlerMiddlewareInterface;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class PassThroughTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    /**
     * @param callable(TaskInterface, TaskRunnerContext): Promise<TaskResultInterface> $next
     * @return Promise<TaskResultInterface>
     */
    public function handle(TaskInterface $task, TaskRunnerContext $runnercontext,callable $next): Promise
    {
        return $next($task, $runnercontext);    
    }
}
```

Configuration:

```yaml
# grumphp.yaml

services:
    PassThroughTaskHandlerMiddleware:
        tags:
            - { name: 'grumphp.task_handler', priority: 500 }
```
