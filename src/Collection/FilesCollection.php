<?php

namespace GrumPHP\Collection;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Util\Regex;
use Symfony\Component\Finder\Comparator;
use Symfony\Component\Finder\Iterator;
use SplFileInfo;
use Traversable;

class FilesCollection extends ArrayCollection
{
    /**
     * Adds a rule that files must match.
     *
     * You can use a pattern (delimited with / sign), a glob or a simple string.
     *
     * $collection->name('*.php')
     * $collection->name('/\.php$/') // same as above
     * $collection->name('test.php')
     *
     * @param string|Regex $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return FilesCollection
     */
    public function name($pattern)
    {
        return $this->names([$pattern]);
    }

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $collection->names(['*.php'])
     * $collection->names(['/\.php$/']) // same as above
     * $collection->names(['test.php'])
     *
     * @param array $patterns Patterns (regexps, globs, or strings)
     *
     * @return FilesCollection
     */
    public function names(array $patterns)
    {
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), $patterns, []);

        return new FilesCollection(iterator_to_array($filter));
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
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), [], [$pattern]);

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Filter by path
     *
     * $collection->path('/^spec\/')
     *
     * @param string $pattern
     *
     * @return FilesCollection
     */
    public function path($pattern)
    {
        return $this->paths([$pattern]);
    }

    /**
     * Filter by paths
     *
     * $collection->paths(['/^spec\/','/^src\/'])
     *
     * @param array $patterns
     *
     * @return FilesCollection
     */
    public function paths(array $patterns)
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), $patterns, []);

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPath('/^spec\/')
     *
     * @param string $pattern
     *
     * @return FilesCollection
     */
    public function notPath($pattern)
    {
        return $this->notPaths([$pattern]);
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPaths(['/^spec\/','/^src\/'])
     *
     * @param array $pattern
     *
     * @return FilesCollection
     */
    public function notPaths(array $pattern)
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), [], $pattern);

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * @param array $extensions
     *
     * @return FilesCollection
     */
    public function extensions(array $extensions)
    {
        if (!count($extensions)) {
            return new FilesCollection();
        }

        return $this->name(sprintf('/\.(%s)$/i', implode('|', $extensions)));
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
        $comparator = new Comparator\NumberComparator($size);
        $filter = new Iterator\SizeRangeFilterIterator($this->getIterator(), [$comparator]);

        return new FilesCollection(iterator_to_array($filter));
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
        $comparator = new Comparator\DateComparator($date);
        $filter = new Iterator\DateRangeFilterIterator($this->getIterator(), [$comparator]);

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Filters the iterator with an anonymous function.
     *
     * The anonymous function receives a \SplFileInfo and must return false
     * to remove files.
     *
     * @param Closure $closure An anonymous function
     *
     * @return FilesCollection The current Finder instance
     *
     * @see CustomFilterIterator
     */
    public function filter(Closure $closure)
    {
        $filter = new Iterator\CustomFilterIterator($this->getIterator(), [$closure]);

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * @param Traversable $fileList
     *
     * @return FilesCollection
     */
    public function filterByFileList(Traversable $fileList)
    {
        $allowedFiles = array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, iterator_to_array($fileList));

        return $this->filter(function (SplFileInfo $file) use ($allowedFiles) {
            return in_array($file->getPathname(), $allowedFiles);
        });
    }

    /**
     * @param FilesCollection $files
     *
     * @return FilesCollection
     */
    public function ensureFiles(FilesCollection $files)
    {
        $newFiles = new self($this->toArray());

        foreach ($files as $file) {
            if (!$newFiles->contains($file)) {
                $newFiles->add($file);
            }
        }

        return $newFiles;
    }
}
