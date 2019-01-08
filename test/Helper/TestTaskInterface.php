<?php /** @noinspection PhpMissingParentConstructorInspection */

namespace GrumPHPTest\Helper;

use GrumPHP\Configuration\GrumPHP;

interface TestTaskInterface
{
    public function setGrumPHP(GrumPHP $grumPhp);
}
