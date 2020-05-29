<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Promise;

use Amp\CancelledException;
use Amp\Delayed;
use Amp\Failure;
use Amp\LazyPromise;
use Amp\Promise;
use Amp\Success;
use GrumPHP\Runner\Promise\MultiPromise;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;

class MultiPromiseTest extends TestCase
{
    use LoopResettingTrait;

    /** @test */
    public function it_does_not_cancel_if_all_good(): void
    {
        [$errors, $results] = wait(MultiPromise::cancelable(
            $this->wrapLazyPromises(
                new Delayed(100, new Success(1)),
                new Delayed(200, new Success(2))
            ),
            function () {
                return false;
            }
        ));

        self::assertSame([], $errors);
        self::assertSame([1, 2], $results);
    }

    /** @test */
    public function it_cancels_on_first_bad_result(): void
    {
        $this->safelyRunAsync(function () {
            [$errors, $results] = wait(MultiPromise::cancelable(
                $this->wrapLazyPromises(
                    new Delayed(100, new Success(1)),
                    new Delayed(200, new Success(2)),
                    new Delayed(300, new Success(3))
                ),
                function () {
                    return true;
                }
            ));

            self::assertCount(2, $errors);
            self::assertInstanceOf(CancelledException::class, $errors[1]);
            self::assertInstanceOf(CancelledException::class, $errors[2]);

            self::assertSame([1], $results);
        });
    }

    /** @test */
    public function it_cancels_on_conditional_bad_result(): void
    {
        $this->safelyRunAsync(function () {
            [$errors, $results] = wait(MultiPromise::cancelable(
                $this->wrapLazyPromises(
                    new Delayed(100, new Success(1)),
                    new Delayed(200, new Success(2)),
                    new Delayed(300, new Success(3))
                ),
                function ($index): bool {
                    return $index == 2;
                }
            ));

            self::assertCount(1, $errors);
            self::assertInstanceOf(CancelledException::class, $errors[2]);

            self::assertSame([1, 2], $results);
        });
    }

    /** @test */
    public function it_cancels_on_exception_thrown(): void
    {
        $this->safelyRunAsync(function () {
            [$errors, $results] = wait(MultiPromise::cancelable(
                $this->wrapLazyPromises(
                    new Delayed(100, new Failure(new \Exception('1'))),
                    new Delayed(200, new Failure(new \Exception('2'))),
                    new Delayed(300, new Failure(new \Exception('3')))
                ),
                function ($index): bool {
                    return false;
                }
            ));

            self::assertCount(3, $errors);
            self::assertInstanceOf(\Exception::class, $errors[0]);
            self::assertInstanceOf(CancelledException::class, $errors[1]);
            self::assertInstanceOf(CancelledException::class, $errors[2]);

            self::assertSame([], $results);
        });
    }

    private function wrapLazyPromises(Promise ... $promises)
    {
        return array_map(
            static function (Promise $promise) {
                return new LazyPromise(function () use ($promise) {
                    return $promise;
                });
            },
            $promises
        );
    }
}
