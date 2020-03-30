<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Container;

use PhpParser\NodeVisitor;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * This class is added to make sure the Symfony container doesn't get serialized for the phpparser task.
 * It will only contain an emty state of the visitor instances that are configured.
 * Every time a visitor is loaded, a cloned version will be provided instead.
 */
class VisitorContainer implements ContainerInterface
{
    /**
     * @var array<string, NodeVisitor>
     */
    private $instances;

    public function __construct(array $instances)
    {
        $this->instances = $instances;
    }

    /**
     * Always provide a cloned version of the visitor to make sure all properties get reset after a parser run.
     * @param string $id
     */
    public function get($id): NodeVisitor
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        return clone $this->instances[$id];
    }

    /**
     * @param string $id
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->instances);
    }
}
