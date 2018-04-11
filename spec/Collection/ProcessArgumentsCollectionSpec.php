<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Exception\InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use SplFileInfo;

class ProcessArgumentsCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProcessArgumentsCollection::class);
    }

    function it_should_be_able_to_create_a_new_collection_based_on_a_command()
    {
        $result = $this->forExecutable('exec');
        $result->shouldBeAnInstanceOf(ProcessArgumentsCollection::class);
        $result->first()->shouldBe('exec');
    }

    function it_should_be_able_to_add_optional_argument()
    {
        $this->addOptionalArgument('--argument=%s', null);
        $this->getValues()->shouldBe([]);

        $this->addOptionalArgument('--argument=%s', 'value');
        $this->getValues()->shouldBe(['--argument=value']);
    }

    function it_should_be_able_to_add_optional_argument_with_separated_value()
    {
        $this->addOptionalArgumentWithSeparatedValue('--argument', null);
        $this->getValues()->shouldBe([]);

        $this->addOptionalArgumentWithSeparatedValue('--argument', 'value');
        $this->getValues()->shouldBe(['--argument', 'value']);
    }

    function it_should_be_able_to_add_optional_comma_separated_argument()
    {
        $this->addOptionalCommaSeparatedArgument('--argument=%s', []);
        $this->getValues()->shouldBe([]);

        $this->addOptionalCommaSeparatedArgument('--argument=%s', [1, 2]);
        $this->getValues()->shouldBe(['--argument=1,2']);
    }

    function it_should_be_able_to_add_an_argument_array()
    {
        $this->addArgumentArray('--item=%s', [1, 2]);
        $this->getValues()->shouldBe([
            '--item=1',
            '--item=2',
        ]);
    }

    function it_should_be_able_to_add_an_argument_array_with_separated_values()
    {
        $this->addArgumentArrayWithSeparatedValue('--item', [1, 2]);
        $this->getValues()->shouldBe([
            '--item',
            1,
            '--item',
            2,
        ]);
    }

    function it_should_be_able_to_add_separated_argument_array()
    {
        $this->addSeparatedArgumentArray('--item', [1, 2]);
        $this->getValues()->shouldBe([
            '--item',
            1,
            2,
        ]);
    }

    function it_should_be_able_to_add_required_argument()
    {
        $this->shouldThrow(InvalidArgumentException::class)->duringAddRequiredArgument('--argument=%s', false);

        $this->addRequiredArgument('--argument=%s', 'value');
        $this->getValues()->shouldBe(['--argument=value']);
    }

    function it_should_be_able_to_add_files()
    {
        $files = new FilesCollection([
            new SplFileInfo('file1.txt'),
            new SplFileInfo('file2.txt')
        ]);
        $this->addFiles($files);

        $this->getValues()->shouldBe([
            'file1.txt',
            'file2.txt',
        ]);
    }

    function it_should_be_able_to_add_comma_separated_files()
    {
        $files = new FilesCollection([
            new SplFileInfo('file1.txt'),
            new SplFileInfo('file2.txt')
        ]);
        $this->addCommaSeparatedFiles($files);

        $this->getValues()->shouldBe(['file1.txt,file2.txt']);
    }

    function it_should_be_able_to_add_an_argument_with_comma_separated_files()
    {
        $files = new FilesCollection([
            new SplFileInfo('file1.txt'),
            new SplFileInfo('file2.txt')
        ]);
        $this->addArgumentWithCommaSeparatedFiles('--argument=%s', $files);

        $this->getValues()->shouldBe(['--argument=file1.txt,file2.txt']);
    }

    function it_should_be_able_to_add_boolean_nullable_argument_with_null_value()
    {
        $this->addOptionalBooleanArgument('--argument=%s', null, 'yes', 'no');
        $this->getValues()->shouldBe([]);
    }

    function it_should_be_able_to_add_boolean_nullable_argument_with_true_value()
    {
        $this->addOptionalBooleanArgument('--argument=%s', true, 'yes', 'no');
        $this->getValues()->shouldBe(['--argument=yes']);
    }

    function it_should_be_able_to_add_boolean_nullable_argument_with_false_value()
    {
        $this->addOptionalBooleanArgument('--argument=%s', false, 'yes', 'no');
        $this->getValues()->shouldBe(['--argument=no']);
    }

    function it_should_be_able_to_add_optional_integer_argument_with_null_value()
    {
        $this->addOptionalIntegerArgument('--argument=%s', null);
        $this->getValues()->shouldBe([]);
    }

    function it_should_be_able_to_add_optional_integer_argument_with_a_value()
    {
        $this->addOptionalIntegerArgument('--argument=%s', 100);
        $this->getValues()->shouldBe(['--argument=100']);
    }

    function it_should_be_able_to_add_optional_integer_argument_with_zero_as_value()
    {
        $this->addOptionalIntegerArgument('--argument=%s', 0);
        $this->getValues()->shouldBe(['--argument=0']);
    }
}
