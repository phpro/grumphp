<?php

namespace spec\GrumPHP\Linter\Yaml;

use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Yaml\YamlLintError;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlLintErrorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'Full Yaml Parser Exception', 'file.txt', 1, 'snippet');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(YamlLintError::class);
    }

    function it_is_a_lint_error()
    {
        $this->shouldHaveType(LintError::class);
    }

    function it_has_a_snippet()
    {
        $this->getSnippet()->shouldBe('snippet');
    }

    function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] Full Yaml Parser Exception');
    }

    function it_can_be_created_from_a_parse_exception()
    {
        $this->beConstructedThrough('fromParseException', [
            new ParseException('Full Yaml Parser Exception', 3, 'snippet', 'file.yml'),
        ]);

        $this->getFile()->shouldBe('file.yml');
        $this->getLine()->shouldBe(3);
        $this->getSnippet()->shouldBe('snippet');
    }

    function it_can_be_created_from_a_parse_exception_without_snippet_and_file()
    {
        $returnType = (new \ReflectionMethod(ParseException::class, 'getSnippet'))->getReturnType();
        if (!$returnType || !$returnType->allowsNull()) {
            throw new SkippingException('This symfony/yaml version does not return null snippets.');
        }

        $this->beConstructedThrough('fromParseException', [
            new ParseException('Full Yaml Parser Exception', 3),
        ]);

        $this->getFile()->shouldBe('');
        $this->getSnippet()->shouldBe('');
    }
}
