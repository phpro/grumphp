<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;

/**
 * @extends AbstractParserTask<PhpParser>
 */
class PhpParser extends AbstractParserTask
{
    /**
     * @var \GrumPHP\Parser\Php\PhpParser
     */
    protected $parser;

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = self::sharedOptionsResolver();
        $resolver->setDefaults([
            'triggered_by' => ['php'],
            'php_version' => null,
            'kind' => null,
            'visitors' => [],
        ]);

        $resolver->setAllowedTypes('php_version', ['string', 'null']);
        $resolver->setDeprecated(
            'kind',
            'phpro/grumphp',
            '2.5',
            'The option "%name%" is deprecated and replaced by the php_version option.'
        );

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $this->parser->setParserOptions($config);

        $parseErrors = $this->parse($files);

        if ($parseErrors->count()) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf(
                    "Some errors occured while parsing your PHP files:\n%s",
                    $parseErrors->__toString()
                )
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
