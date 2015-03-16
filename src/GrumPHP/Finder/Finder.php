<?php

namespace GrumPHP\Finder;

use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * This class is created because it is impossible to mock the symfony finder in phpspec.
 * The finder object shouldn't have any state in this project.
 *
 * Class Finder
 *
 * @package GrumPHP\Finder
 */
class Finder
{
    /**
     * @param array|\Iterator|\Traversable|\IteratorAggregate $files
     *
     * @return SymfonyFinder
     */
    public function create($files)
    {
        $newFinder = SymfonyFinder::create();
        $newFinder->append($files);
        return $newFinder;
    }
}
