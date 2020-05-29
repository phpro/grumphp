<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Fixer;

use GrumPHP\Fixer\FixResult;
use PHPUnit\Framework\TestCase;

class FixResultTest extends TestCase
{
    /** @test */
    public function it_can_contain_a_result(): void
    {
        $result = FixResult::success('success');
        self::assertTrue($result->ok());
        self::assertNull($result->error());
        self::assertSame('success', $result->result());
    }

    /** @test */
    public function it_can_contain_an_error(): void
    {
        $result = FixResult::failed($error = new \Exception('error'));
        self::assertFalse($result->ok());
        self::assertSame($error, $result->error());
        self::assertNull($result->result());
    }
}
