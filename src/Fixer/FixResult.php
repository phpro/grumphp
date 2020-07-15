<?php

declare(strict_types=1);

namespace GrumPHP\Fixer;

/**
 * @template TResult
 * @psalm-readonly
 */
class FixResult
{
    /**
     * @var \Throwable|null
     */
    private $error;

    /**
     * @var TResult|null
     */
    private $result;

    /**
     * @param TResult|null $result
     */
    private function __construct($result, ?\Throwable $error)
    {
        $this->error = $error;
        $this->result = $result;
    }

    public static function failed(\Throwable $error): self
    {
        return new self(null, $error);
    }

    /**
     * @param mixed $result
     */
    public static function success($result): self
    {
        return new self($result, null);
    }

    public function ok(): bool
    {
        return null === $this->error;
    }

    /**
     * @return TResult|null
     */
    public function result()
    {
        return $this->result;
    }

    public function error(): ?\Throwable
    {
        return $this->error;
    }
}
