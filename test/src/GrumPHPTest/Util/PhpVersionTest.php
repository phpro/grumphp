<?php

namespace GrumPHPTest\Util;

use GrumPHP\Util\PhpVersion;

/**
 * Class PhpVersionTest
 * @package GrumPHPTest\Util
 */
class PhpVersionTest extends \PHPUnit_Framework_TestCase
{
    public function testValidVersion()
    {
        $now = new \DateTime();
        $inOneYear = $now->add(new \DateInterval('P1Y'));
        $versions = ['5.6' => $inOneYear->format('Y-m-d H:i:s')];
        $util = new PhpVersion($versions);
        $this->assertTrue($util->isSupportedVersion('5.6'));

        // Also test with multiple versions
        $now = new \DateTime();
        $inOneYear = $now->add(new \DateInterval('P1Y'));
        $versions = [
            '5.6' => $inOneYear->format('Y-m-d H:i:s'),
            '7.0' => $inOneYear->format('Y-m-d H:i:s'),
        ];
        $util = new PhpVersion($versions);
        $this->assertTrue($util->isSupportedVersion('5.6'));
    }

    public function testUnsupportedVersion()
    {
        $now = new \DateTime();
        $inOneYear = $now->add(new \DateInterval('P1Y'));
        $versions = ['5.6' => $inOneYear->format('Y-m-d H:i:s')];
        $util = new PhpVersion($versions);
        $this->assertFalse($util->isSupportedVersion('5.5'));
    }

    public function testExpiredVersion()
    {
        $now = new \DateTime();
        $oneYearAgo = $now->sub(new \DateInterval('P1Y'));
        $versions = ['5.6' => $oneYearAgo->format('Y-m-d H:i:s')];
        $util = new PhpVersion($versions);
        $this->assertFalse($util->isSupportedVersion('5.6'));
    }

    public function testProjectVersion()
    {
        $util = new PhpVersion([]);
        $this->assertFalse($util->isSupportedProjectVersion('5.6', '5.7'));
        $this->assertTrue($util->isSupportedProjectVersion('5.7', '5.7'));
        $this->assertTrue($util->isSupportedProjectVersion('5.8', '5.7'));
        $this->assertTrue($util->isSupportedProjectVersion('5.5.9-1ubuntu4.20', '5.5'));
        $this->assertFalse($util->isSupportedProjectVersion('5.5.9-1ubuntu4.20', '5.6'));
    }
}
