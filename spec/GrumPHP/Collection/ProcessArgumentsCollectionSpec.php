<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Collection\FilesCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SplFileInfo;

class ProcessArgumentsCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Collection\ProcessArgumentsCollection');
    }

    function it_should_be_able_to_create_a_new_collection_based_on_a_command()
    {
        $result = $this->forExecutable('exec');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\ProcessArgumentsCollection');
        $result->first()->shouldBe('exec');
    }

    function it_should_be_able_to_add_optional_argument()
    {
        $this->addOptionalArgument('--argument=%s', null);
        $this->getValues()->shouldBe(array());

        $this->addOptionalArgument('--argument=%s', 'value');
        $this->getValues()->shouldBe(array('--argument=value'));
    }

    function it_should_be_able_to_add_optional_comma_separated_argument()
    {
        $this->addOptionalCommaSeparatedArgument('--argument=%s', array());
        $this->getValues()->shouldBe(array());

        $this->addOptionalCommaSeparatedArgument('--argument=%s', array(1, 2));
        $this->getValues()->shouldBe(array('--argument=1,2'));
    }

    function it_should_be_able_to_add_an_argument_array()
    {
        $this->addArgumentArray('--item=%s', array(1, 2));
        $this->getValues()->shouldBe(array(
            '--item=1',
            '--item=2',
        ));
    }

    function it_should_be_able_to_add_required_argument()
    {
        $this->shouldThrow('GrumPHP\Exception\InvalidArgumentException')->duringAddRequiredArgument('--argument=%s', false);

        $this->addRequiredArgument('--argument=%s', 'value');
        $this->getValues()->shouldBe(array('--argument=value'));
    }

    function it_should_be_able_to_add_files()
    {
        $files = new FilesCollection(array(
            new SplFileInfo('file1.txt'),
            new SplFileInfo('file2.txt')
        ));
        $this->addFiles($files);

        $this->getValues()->shouldBe(array(
            'file1.txt',
            'file2.txt',
        ));
    }
}
