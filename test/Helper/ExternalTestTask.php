<?php /** @noinspection PhpMissingParentConstructorInspection */

namespace GrumPHPTest\Helper;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\RawProcessFormatter;
use GrumPHP\Process\ProcessFactory;
use GrumPHP\Task\AbstractExternalParallelTask;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class ExternalTestTask extends AbstractExternalParallelTask
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int|null
     */
    protected $returnCode;
    /**
     * @var string|null
     */
    protected $stdOutString;
    /**
     * @var string|null
     */
    protected $stdErrString;
    /**
     * @var int|null
     */
    protected $runtime;
    /**
     * @var int|null
     */
    protected $stage;

    /**
     * ExternalTestTask constructor.
     *
     * @param string $name
     * @param int|null $returnCode
     * @param string|null $stdOutString
     * @param string|null $stdErrString
     * @param int|null $runtime
     * @param int|null $stage
     */
    public function __construct(
        string $name,
        int $returnCode = null,
        string $stdOutString = null,
        string $stdErrString = null,
        int $runtime = null,
        int $stage = null
    ) {
        $this->name         = $name;
        $this->returnCode   = $returnCode;
        $this->stdOutString = $stdOutString;
        $this->stdErrString = $stdErrString;
        $this->runtime      = $runtime;
        $this->stage        = $stage ?? 0;

        $this->formatter = new RawProcessFormatter();
    }

    /**
     * Override in Task
     *
     * @param string $command
     * @param  array $config
     * @param ContextInterface $context
     * @return ProcessArgumentsCollection
     */
    protected function buildArguments(string $command, array $config, ContextInterface $context): ProcessArgumentsCollection
    {
        $executable = __DIR__."/process_helper";

        $args = new ProcessArgumentsCollection();
        $args->add("php");
        $args->add($executable);
        $args->addOptionalArgument('-c=%s', $this->returnCode);
        $args->addOptionalArgument('-o=%s', $this->stdOutString);
        $args->addOptionalArgument('-e=%s', $this->stdErrString);
        $args->addOptionalArgument('-t=%s', (string) $this->runtime);
        return $args;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param ContextInterface $context
     * @return Process
     */
    public function resolveProcess(ContextInterface $context): Process
    {
        $executable = $this->getExecutableName();
        $arguments  = $this->buildArguments($executable, [], $context);
        $process    = ProcessFactory::fromArguments($arguments);
        return $process;
    }

    /**
     * Dummy
     *
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        return new OptionsResolver();
    }

    /**
     * Dummy
     *
     * @param ContextInterface $context
     * @return bool
     */

    public function canRunInContext(ContextInterface $context): bool
    {
        return true;
    }

    public function getStage(): int
    {
        return $this->stage;
    }
}
