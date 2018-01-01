<?php

namespace spec\GrumPHP\Process;

use GrumPHP\Process\ProcessUtils;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;

class ProcessUtilsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProcessUtils::class);
    }

    function it_escapes_arguments_on_windows_platform()
    {
        if ('\\' !== DIRECTORY_SEPARATOR) {
            throw new SkippingException('Test requires a Windows platform.');
        }

        self::escapeArgument("'")->shouldReturn("'");
        self::escapeArgument('éÉèÈàÀöä')->shouldReturn('éÉèÈàÀöä');

        self::escapeArgument('')->shouldReturn('""');
        self::escapeArgument('a"b%c%')->shouldReturn('"a""b"^%"c"^%""');
        self::escapeArgument('a"b^c^')->shouldReturn('"a""b"^^"c"^^""');
        self::escapeArgument("a\nb'c")->shouldReturn('"a!LF!b\'c"');
        self::escapeArgument('a^b c!')->shouldReturn('"a"^^"b c"^!""');
        self::escapeArgument("a!b\tc")->shouldReturn("\"a\"^!\"b\tc\"");
        self::escapeArgument('a\\\\"\\"')->shouldReturn('"a\\\\""\"""');
    }

    function it_escapes_arguments_on_other_platforms()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            throw new SkippingException('Test requires a non-Windows platform.');
        }

        self::escapeArgument('')->shouldReturn("''");
        self::escapeArgument("'")->shouldReturn("''\\'''");
        self::escapeArgument('a"b%c%')->shouldReturn("'a\"b%c%'");
        self::escapeArgument('a"b^c^')->shouldReturn("'a\"b^c^'");
        self::escapeArgument("a\nb'c")->shouldReturn("'a\nb'\''c'");
        self::escapeArgument('a^b c!')->shouldReturn("'a^b c!'");
        self::escapeArgument("a!b\tc")->shouldReturn("'a!b\tc'");
        self::escapeArgument('a\\\\"\\"')->shouldReturn("'a\\\\\"\\\"'");
        self::escapeArgument('éÉèÈàÀöä')->shouldReturn("'éÉèÈàÀöä'");
    }
}
