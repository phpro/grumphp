<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Parser\Php\PhpParserError;
use GrumPHP\Collection\ParseErrorsCollection;
use Symfony\Component\DependencyInjection\Container;

class NodeVisitorAbstract extends \PhpParser\NodeVisitorAbstract
{
    protected $options = [];
    protected $filename;
    protected $errors;

    protected $blacklist = [];
    protected $whitelist = [];

    public function __construct(GrumPHP $grumPHP)
    {
        $taskConfig = $grumPHP->getTaskConfiguration('php_parser');

        // visitor options identifier:
        $parts = explode('\\', get_class($this));
        $id = Container::underscore(end($parts));

        if (isset($taskConfig['visitors_options'][$id])
            && is_array($taskConfig['visitors_options'][$id])
        ) {
            $this->options = $taskConfig['visitors_options'][$id];
        }

        if (isset($this->options['blacklist'])) {
            $this->blacklist = (array) $this->options['blacklist'];
        }
        if (isset($this->options['whitelist'])) {
            $this->whitelist = (array) $this->options['whitelist'];
        }
    }

    public function init($filename, ParseErrorsCollection $errors)
    {
        $this->filename  = $filename;
        $this->errors    = $errors;
    }

    protected function addError($message, $item, $node)
    {
        if (in_array($item, $this->blacklist)) {
            $level = PhpParserError::TYPE_ERROR;
        } elseif (in_array($item, $this->whitelist)) {
            $level = PhpParserError::TYPE_WARNING;
        } else {
            $level = PhpParserError::TYPE_NOTICE;
        }

        $this->errors->add(
            new PhpParserError(
                $level,
                $message,
                $this->filename,
                $node->getLine()
            )
        );
    }
}
