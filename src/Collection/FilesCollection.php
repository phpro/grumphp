<?php declare(strict_types=1);

namespace GrumPHP\Collection;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Util\Regex;
use SplFileInfo;
use Symfony\Component\Finder\Comparator;
use Symfony\Component\Finder\Iterator;
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
     */
    public function name($pattern): self
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
     */
    public function names(array $patterns): self
    {
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), $patterns, []);

        return new self(iterator_to_array($filter));
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
     */
    public function notName(string $pattern): self
    {
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), [], [$pattern]);

        return new self(iterator_to_array($filter));
    }

    /**
     * Filter by path
     *
     * $collection->path('/^spec\/')
     */
    public function path(string $pattern): self
    {
        return $this->paths([$pattern]);
    }

    /**
     * Filter by paths
     *
     * $collection->paths(['/^spec\/','/^src\/'])
     */
    public function paths(array $patterns): self
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), $patterns, []);

        return new self(iterator_to_array($filter));
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPath('/^spec\/')
     */
    public function notPath(string $pattern): self
    {
        return $this->notPaths([$pattern]);
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPaths(['/^spec\/','/^src\/'])
     */
    public function notPaths(array $pattern): self
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), [], $pattern);

        return new self(iterator_to_array($filter));
    }

    /**
     * @return FilesCollection
     */
    public function extensions(array $extensions): self
    {
        if (!count($extensions)) {
            return new self();
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
     *
     * @see NumberComparator
     */
    public function size(string $size): self
    {
        $comparator = new Comparator\NumberComparator($size);
        $filter = new Iterator\SizeRangeFilterIterator($this->getIterator(), [$comparator]);

        return new self(iterator_to_array($filter));
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
     *
     * @see DateComparator
     */
    public function date(string $date): self
    {
        $comparator = new Comparator\DateComparator($date);
        $filter = new Iterator\DateRangeFilterIterator($this->getIterator(), [$comparator]);

        return new self(iterator_to_array($filter));
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
    public function filter(Closure $closure): self
    {
        $filter = new Iterator\CustomFilterIterator($this->getIterator(), [$closure]);

        return new self(iterator_to_array($filter));
    }

    /**
     * @return FilesCollection
     */
    public function filterByFileList(Traversable $fileList): self
    {
        $allowedFiles = array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, iterator_to_array($fileList));

        return $this->filter(function (SplFileInfo $file) use ($allowedFiles) {
            return in_array($file->getPathname(), $allowedFiles);
        });
    }

    /**
     * @return FilesCollection
     */
    public function ensureFiles(self $files): self
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
