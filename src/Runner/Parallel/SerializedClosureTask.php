<?php
declare(strict_types=1);

namespace GrumPHP\Runner\Parallel;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;
use Laravel\SerializableClosure\SerializableClosure;

/**
 * @template-covariant TResult
 * @template TReceive
 * @template TSend
 *
 * @implements Task<TResult, TReceive, TSend>
 */
class SerializedClosureTask implements Task
{
    /**
     * @param (\Closure(): TResult) $closure
     */
    private function __construct(
        private string $serializedClosure
    ) {
    }

    /**
     * @template CResult
     * @template CReceive
     * @template CSend
     *
     * @param (\Closure(): CResult) $closure
     * @return self<CResult, CReceive, CSend>
     */
    public static function fromClosure(\Closure $closure): self
    {
        return new self(serialize(new SerializableClosure($closure)));
    }

    /**
     * @return TResult
     */
    public function run(Channel $channel, Cancellation $cancellation): mixed
    {
        $unserialized = \unserialize($this->serializedClosure, ['allowed_classes' => true]);

        if ($unserialized instanceof \__PHP_Incomplete_Class) {
            throw new \Error(
                'When using a class instance as a callable, the class must be autoloadable'
            );
        }

        if (!$unserialized instanceof SerializableClosure) {
            throw new \Error(
                'This task can only deal with serialized closures. You passed '.get_debug_type($unserialized)
            );
        }

        $closure = $unserialized->getClosure();

        return $closure();
    }
}
