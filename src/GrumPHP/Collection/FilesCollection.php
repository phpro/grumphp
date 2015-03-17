<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use SplFileInfo;
use Symfony\Component\Finder\Comparator\DateComparator;
use Symfony\Component\Finder\Comparator\NumberComparator;
use Symfony\Component\Finder\Expression\Expression;

/**
 * Class FileSequence
 *
 * @package GrumPHP\Collection
 */
class FilesCollection extends ArrayCollection
{

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $collection->name('*.php')
     * $collection->name('/\.php$/') // same as above
     * $collection->name('test.php')
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return FilesCollection
     */
    public function name($pattern)
    {
        $regex = Expression::create($pattern)->getRegex()->render();
        return $this->filter(function (SplFileInfo $file) use ($regex) {
            return preg_match($regex, $file->getFilename());
        });
    }

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $collection->name('*.php')
     * $collection->name('/\.php$/') // same as above
     * $collection->name('test.php')
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return FilesCollection
     */
    public function notName($pattern)
    {
        $regex = Expression::create($pattern)->getRegex()->render();
        return $this->filter(function (SplFileInfo $file) use ($regex) {
            return !preg_match($regex, $file->getFilename());
        });
    }

    /**
     * Filter by path
     *
     * $collection->path('/^spec\/')
     *
     * @param $pattern
     *
     * @return FilesCollection
     */
    public function path($pattern)
    {
        $regex = Expression::create($pattern)->getRegex()->render();
        return $this->filter(function (SplFileInfo $file) use ($regex) {
            return preg_match($regex, $file->getPath());
        });
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPath('/^spec\/')
     *
     * @param $pattern
     *
     * @return FilesCollection
     */
    public function notPath($pattern)
    {
        $regex = Expression::create($pattern)->getRegex()->render();
        return $this->filter(function (SplFileInfo $file) use ($regex) {
            return !preg_match($regex, $file->getPath());
        });
    }

    /**
     * Adds tests for file sizes.
     *
     * $collection->filterBySize('> 10K');
     * $collection->filterBySize('<= 1Ki');
     * $collection->filterBySize(4);
     *
     * @param string $size A size range string
     *
     * @return FilesCollection
     *
     * @see NumberComparator
     */
    public function size($size)
    {
        $comparator = new NumberComparator($size);
        return $this->filter(function (SplFileInfo $file) use ($comparator) {
            return $comparator->test($file->getSize());
        });
    }

    /**
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     * $collection->filterByDate('since yesterday');
     * $collection->filterByDate('until 2 days ago');
     * $collection->filterByDate('> now - 2 hours');
     * $collection->filterByDate('>= 2005-10-15');
     *
     * @param string $date A date to test
     *
     * @return FilesCollection
     *
     * @see DateComparator
     */
    public function date($date)
    {
        $comparator = new DateComparator($date);
        return $this->filter(function (SplFileInfo $file) use ($comparator) {
            return $comparator->test($file->getMTime());
        });
    }
}
