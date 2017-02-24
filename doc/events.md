# Events

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
