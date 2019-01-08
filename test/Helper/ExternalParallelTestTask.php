<?php /** @noinspection PhpMissingParentConstructorInspection */

namespace GrumPHPTest\Helper;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\RawProcessFormatter;
use GrumPHP\Process\ProcessFactory;
use GrumPHP\Task\AbstractExternalParallelTask;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class ExternalParallelTestTask extends AbstractExternalParallelTask implements TestTaskInterface
{
    use ExternalTestTaskTrait;
}
