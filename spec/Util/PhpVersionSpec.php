<?php

namespace spec\GrumPHP\Util;

use PhpSpec\ObjectBehavior;

class PhpVersionSpec extends ObjectBehavior
{
    function let()
    {
        $nextYear = new \DateTime();
        $previousYear = new \DateTime();
        $oneYear = new \DateInterval('P1Y');
        $this->beConstructedWith([
            '5.3' => $previousYear->sub($oneYear)->format('Y-m-d H:i:s'),
            '5.6' => $nextYear->add($oneYear)->format('Y-m-d H:i:s'),
        ]);
    }

    function it_should_validate_php_version()
    {
        $this->isSupportedVersion(5.3)->shouldReturn(false);
        $this->isSupportedVersion(5.6)->shouldReturn(true);
    }

    function it_should_validate_project_version()
    {
        $this->isSupportedProjectVersion(5.3, 5.6)->shouldReturn(false);
        $this->isSupportedProjectVersion(7, 5.6)->shouldReturn(true);
        $this->isSupportedProjectVersion('5.6', '5.7')->shouldReturn(false);
        $this->isSupportedProjectVersion('5.7', '5.7')->shouldReturn(true);
        $this->isSupportedProjectVersion('5.8', '5.7')->shouldReturn(true);
        $this->isSupportedProjectVersion('5.5.9-1ubuntu4.20', '5.5')->shouldReturn(true);
        $this->isSupportedProjectVersion('5.5.9-1ubuntu4.20', '5.6')->shouldReturn(false);
    }

    public function testUnsupportedVersion()
    {
        $this->isSupportedVersion(5.5)->shouldReturn(false);
    }

    public function testExpiredVersion()
    {
        $this->isSupportedVersion(5.3)->shouldReturn(false);
    }
}
