<?php
declare(strict_types=1);

namespace GrumPHPTest\Helpers;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\ContextInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Finder\SplFileInfo;

trait ContextMocks
{
    use ProphecyTrait;

    protected function mockContext(string $class = ContextInterface::class, array $files = []): ContextInterface
    {
        /** @var ContextInterface|ObjectProphecy $context */
        $context = $this->prophesize($class);
        $context->getFiles()->willReturn(
            new FilesCollection(
                array_map(
                    static function ($file): SplFileInfo {
                        return $file instanceof SplFileInfo ? $file : new SplFileInfo($file, $file, $file);
                    },
                    $files
                )
            )
        );

        return $context->reveal();
    }
}
