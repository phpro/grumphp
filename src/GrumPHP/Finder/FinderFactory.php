<?php

namespace GrumPHP\Finder;

use Symfony\Component\Finder\Finder;

/**
 * This class is created because it is impossible to use the statefull Symfony finder
 *
 * Class Finder
 *
 * @package GrumPHP\Finder
 */
class FinderFactory
{
    /**
     * @param array|\Iterator|\Traversable|\IteratorAggregate|Finder $files
     *
     * @return Finder
     */
    public function create($files)
    {
        $newFinder = Finder::create();
        $newFinder->append($files);
        return $newFinder;
    }
}
