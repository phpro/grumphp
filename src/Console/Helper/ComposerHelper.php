<?php

declare(strict_types=1);

namespace GrumPHP\Console\Helper;

use GrumPHP\Util\ComposerFile;
use Symfony\Component\Console\Helper\Helper;

class ComposerHelper extends Helper
{
    const HELPER_NAME = 'composer';

    private $composerFile;

    public function __construct(ComposerFile $composerFile)
    {
        $this->composerFile = $composerFile;
    }

    public function getComposerFile(): ComposerFile
    {
        return $this->composerFile;
    }

    public function getName(): string
    {
        return self::HELPER_NAME;
    }
}
