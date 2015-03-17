<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use SplFileInfo;

/**
 * Class FileSequence
 *
 * @package GrumPHP\Collection
 */
class FilesCollection extends ArrayCollection
{

    /**
     * Filter files by name
     *
     * @param string $pattern
     *
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function filterByName($pattern)
    {
        return $this->filter(function (SplFileInfo $file) use ($pattern) {
            return preg_match($pattern, $file->getPathname());
        });
    }

} 