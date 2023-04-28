<?php
declare(strict_types=1);

namespace GrumPHP\Runner\Parallel;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;
use Laravel\SerializableClosure\SerializableClosure;

/**
 * @template T
 */
class SerializedClosureTask implements Task
{
    /**
     * @param (\Closure(): T) $closure
     */
    public function __construct(
        private string $serializedClosure
    ) {
    }

    /**
     * @template O
     * @param \Closure(): O $closure
     * @return self<O>
     */
    public static function fromClosure(\Closure $closure): self
    {
        return new self(serialize(SerializableClosure::unsigned($closure)));
    }

    /**
     * @return T
     */
    public function run(Channel $channel, Cancellation $cancellation): mixed
    {
        $callable = \unserialize($this->serializedClosure, ['allowed_classes' => true]);

        if ($callable instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance as a callable, the class must be autoloadable');
        }

        if (!is_object($callable) || !is_callable($callable)) {
            throw new \Error('This task can only deal with serialized Closures. You passed '.get_debug_type($callable));
        }

        return $callable();
    }
}
