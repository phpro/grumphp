<?php
namespace Amp {

    /**
     * @template TReturn
     *
     * @param callable():\Generator<mixed, mixed, mixed, TReturn> $gen
     *
     * @return callable():Promise<TReturn>
     */
    function coroutine(callable $gen) : callable
    {
    }

    /**
     * @template TReturn
     *
     * @param callable():(\Generator<mixed, mixed, mixed, TReturn>|TReturn) $gen
     *
     * @return Promise<TReturn>
     */
    function call(callable $gen) : Promise
    {
    }


    /**
     * @template TReturn
     */
    interface Promise
    {
        /**
         * @param callable(?\Throwable, ?TReturn):void $onResolved
         *
         * @return void
         */
        public function onResolve(callable $onResolved);
    }

    /**
     * @template TReturn
     */
    final class LazyPromise
    {
        /**
         * @param callable(?\Throwable, ?TReturn):void $onResolved
         *
         * @return void
         */
        public function onResolve(callable $onResolved)
        {
        }
    }

    /**
     * @template TReturn
     *
     * @template-implements Promise<TReturn>
     */
    class Success implements Promise
    {
        /**
         * @param TReturn|null $value
         */
        public function __construct($value = null)
        {
        }

        /**
         * @param callable(?Throwable, ?TReturn):void $onResolved
         *
         * @return void
         */
        public function onResolve(callable $onResolved)
        {
        }
    }
}

namespace Amp\Promise {
    use React\Promise\Promise as ReactPromise;
    use Amp\Promise;

    /**
     * @template TPromise
     * @template T as Promise<TPromise>|ReactPromise
     *
     * @param Promise|ReactPromise $promise Promise to wait for.
     *
     * @return mixed Promise success value.
     *
     * @psalm-param T              $promise
     * @psalm-return (T is Promise ? TPromise : mixed)
     *
     * @throws \TypeError If $promise is not an instance of \Amp\Promise or \React\Promise\PromiseInterface.
     * @throws \Error If the event loop stopped without the $promise being resolved.
     * @throws \Throwable Promise failure reason.
     */
    function wait($promise){}

    /**
     * @template TValue
     *
     * @param Promise<TValue>[]|\React\Promise\PromiseInterface[] $promises
     *
     * @return Promise<array{0: \Throwable[], 1: TValue[]}>
     *
     * @throws \Error If a non-Promise is in the array.
     */
    function any(array $promises): Promise {}
}


