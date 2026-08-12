<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Util\Regex;
use SplFileInfo;
use Traversable;

/**
 * @extends ArrayCollection<array-key, string>
 */
class FilesCollection extends ArrayCollection
{
    /**
     * @deprecated use FilesCollectionFilter::name directly
     *
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
        return FilesCollectionFilter::name($this, $pattern);
    }

    /**
     * @deprecated use FilesCollectionFilter::names directly
     *
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $collection->names(['*.php'])
     * $collection->names(['/\.php$/']) // same as above
     * $collection->names(['test.php'])
     */
    public function names(array $patterns): self
    {
        return FilesCollectionFilter::names($this, $patterns);
    }

    /**
     * @deprecated use FilesCollectionFilter::notName directly
     *
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $collection->name('*.php')
     * $collection->name('/\.php$/') // same as above
     * $collection->name('test.php')
     */
    public function notName(string $pattern): self
    {
        return FilesCollectionFilter::notName($this, $pattern);
    }

    /**
     * @deprecated use FilesCollectionFilter::path directly
     *
     * Filter by path.
     *
     * $collection->path('/^spec\/')
     */
    public function path(string $pattern): self
    {
        return FilesCollectionFilter::path($this, $pattern);
    }

    /**
     * @deprecated use FilesCollectionFilter::paths directly
     *
     * Filter by paths.
     *
     * $collection->paths(['/^spec\/','/^src\/'])
     */
    public function paths(array $patterns): self
    {
        return FilesCollectionFilter::paths($this, $patterns);
    }

    /**
     * @deprecated use FilesCollectionFilter::notPath directly
     *
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPath('/^spec\/')
     */
    public function notPath(string $pattern): self
    {
        return FilesCollectionFilter::notPath($this, $pattern);
    }

    /**
     * @deprecated use FilesCollectionFilter::notPaths directly
     *
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPaths(['/^spec\/','/^src\/'])
     */
    public function notPaths(array $pattern): self
    {
        return FilesCollectionFilter::notPaths($this, $pattern);
    }

    /**
     * @deprecated use FilesCollectionFilter::extensions directly
     */
    public function extensions(array $extensions): self
    {
        return FilesCollectionFilter::extensions($this, $extensions);
    }

    /**
     * @deprecated use FilesCollectionFilter::size directly
     *
     * Adds tests for file sizes.
     *
     * $collection->filterBySize('> 10K');
     * $collection->filterBySize('<= 1Ki');
     * $collection->filterBySize(4);
     *
     * @see NumberComparator
     */
    public function size(string $size): self
    {
        return FilesCollectionFilter::size($this, $size);
    }

    /**
     * @deprecated use FilesCollectionFilter::date directly
     *
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     * $collection->filterByDate('since yesterday');
     * $collection->filterByDate('until 2 days ago');
     * $collection->filterByDate('> now - 2 hours');
     * $collection->filterByDate('>= 2005-10-15');
     *
     * @see DateComparator
     */
    public function date(string $date): self
    {
        return FilesCollectionFilter::date($this, $date);
    }

    /**
     * @deprecated use FilesCollectionFilter::filter directly
     *
     * Filters the iterator with an anonymous function.
     *
     * The anonymous function receives a \SplFileInfo and must return false
     * to remove files.
     *
     * @see CustomFilterIterator
     *
     * @psalm-suppress LessSpecificImplementedReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    public function filter(Closure $p): self
    {
        return FilesCollectionFilter::filter($this, $p);
    }

    /**
     * @deprecated use FilesCollectionFilter::filterByFileList directly
     *
     * @param Traversable<array-key, SplFileInfo> $fileList
     */
    public function filterByFileList(Traversable $fileList): self
    {
        return FilesCollectionFilter::filterByFileList($this, $fileList);
    }

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

    /**
     * @deprecated use FilesCollectionFilter::ignoreSymlinks directly
     */
    public function ignoreSymlinks(): self
    {
        return FilesCollectionFilter::ignoreSymlinks($this);
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->clear();
        foreach ($data as $path) {
            $this->add($path);
        }
    }

    public function toFileList(): string
    {
        return \implode(PHP_EOL, $this->toArray());
    }
}
