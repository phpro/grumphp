<?php

namespace GrumPHP\Locator;

use GitElephant\Objects\Diff\Diff;
use GitElephant\Objects\Diff\DiffObject;
use GitElephant\Repository;

/**
 * Class Git
 *
 * @package GrumPHP\Locator
 */
class ChangedFiles implements LocatorInterface
{

    const PATTERN_ALL = '/(.*)/';
    const PATTERN_PHP = '/(.*)\.php$/i';

    /**
     * @var string
     */
    protected $gitDir;

    /**
     * @var Diff
     */
    protected $diff;

    /**
     * @param $gitDir
     */
    public function __construct($gitDir)
    {
        $this->gitDir = $gitDir;
    }

    /**
     * @return Diff
     */
    public function getDiff()
    {
        if (!$this->diff) {
            $repository = Repository::open($this->gitDir);
            $this->diff = $repository->getDiff();
        }
        return $this->diff;
    }

    /**
     * @param $pattern
     *
     * @return array|void
     */
    public function locate($pattern = self::PATTERN_ALL)
    {
        $diff = $this->getDiff();
        $files = array();

        /** @var DiffObject $change */
        foreach ($diff as $change) {

            $path = $change->hasPathChanged() ? $change->getDestinationPath() : $change->getOriginalPath();
            if (!preg_match($pattern, $path)) {
                continue;
            }

            $files[] = $path;
        }

        return $files;
    }
}
