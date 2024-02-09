<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Util\Regex;
use Symfony\Component\Finder\Comparator;
use Symfony\Component\Finder\Iterator;
use SplFileInfo;
use Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use Traversable;

/**
 * @extends ArrayCollection<array-key, \SplFileInfo>
 */
class FilesCollection extends ArrayCollection
{
    /**
     * @param \Traversable<array-key, \SplFileInfo> $iterator
     */
    public static function fromTraversable(\Traversable $iterator): FilesCollection
    {
        return new self(array_values(iterator_to_array($iterator)));
    }

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
     */
    public function names(array $patterns): self
    {
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), $patterns, []);

        return self::fromTraversable($filter);
    }

    /**
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
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), [], [$pattern]);

        return self::fromTraversable($filter);
    }

    /**
     * Filter by path.
     *
     * $collection->path('/^spec\/')
     */
    public function path(string $pattern): self
    {
        return $this->paths([$pattern]);
    }

    /**
     * Filter by paths.
     *
     * $collection->paths(['/^spec\/','/^src\/'])
     *
     * @psalm-suppress ArgumentTypeCoercion - Works on int, \SplFileInfo as well
     */
    public function paths(array $patterns): self
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), $patterns, []);

        return self::fromTraversable($filter);
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
     *
     * @psalm-suppress ArgumentTypeCoercion - Works on int, \SplFileInfo as well
     */
    public function notPaths(array $pattern): self
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), [], $pattern);

        return self::fromTraversable($filter);
    }

    public function extensions(array $extensions): self
    {
        if (!\count($extensions)) {
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
     *
     *
     * @see NumberComparator
     */
    public function size(string $size): self
    {
        $comparator = new Comparator\NumberComparator($size);
        $filter = new Iterator\SizeRangeFilterIterator($this->getIterator(), [$comparator]);

        return self::fromTraversable($filter);
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
     *
     *
     * @see DateComparator
     */
    public function date(string $date): self
    {
        $comparator = new Comparator\DateComparator($date);
        $filter = new Iterator\DateRangeFilterIterator($this->getIterator(), [$comparator]);

        return self::fromTraversable($filter);
    }

    /**
     * Filters the iterator with an anonymous function.
     *
     * The anonymous function receives a \SplFileInfo and must return false
     * to remove files.
     *
     *
     *
     * @see CustomFilterIterator
     *
     * @psalm-suppress LessSpecificImplementedReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    public function filter(Closure $p): self
    {
        $filter = new Iterator\CustomFilterIterator($this->getIterator(), [$p]);

        return self::fromTraversable($filter);
    }

    /**
     * @param Traversable<array-key, SplFileInfo> $fileList
     */
    public function filterByFileList(Traversable $fileList): FilesCollection
    {
        $allowedFiles = array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, iterator_to_array($fileList));

        return $this->filter(function (SplFileInfo $file) use ($allowedFiles) {
            return \in_array($file->getPathname(), $allowedFiles, true);
        });
    }

    public function ensureFiles(self $files): FilesCollection
    {
        $newFiles = new self($this->toArray());

        foreach ($files as $file) {
            if (!$newFiles->contains($file)) {
                $newFiles->add($file);
            }
        }

        return $newFiles;
    }

    public function ignoreSymlinks(): FilesCollection
    {
        return $this->filter(function (SplFileInfo $file) {
            return !$file->isLink();
        });
    }

    public function ignoreDeleted(): FilesCollection
    {
        return $this->filter(function (\SplFileInfo $file) {
            return $file->isFile();
        });
    }

    /**
     * SplFileInfo cannot be serialized. Therefor, we help PHP a bit.
     * This stuff is used for running tasks in parallel.
     */
    public function __serialize(): array
    {
        return $this->map(function (SplFileInfo $fileInfo): string {
            return $fileInfo instanceof SymfonySplFileInfo
                ? $fileInfo->getRelativePathname()
                : $fileInfo->getPathname();
        })->toArray();
    }

    /**
     * SplFileInfo cannot be serialized. Therefor, we help PHP a bit.
     * This stuff is used for running tasks in parallel.
     */
    public function __unserialize(array $data): void
    {
        $files = $data;
        $this->clear();
        foreach ($files as $file) {
            $this->add(new SymfonySplFileInfo($file, dirname($file), $file));
        }
    }

    /**
     * Help Psalm out a bit:
     *
     * @return \ArrayIterator<array-key, SplFileInfo>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->toArray());
    }

    public function toFileList(): string
    {
        return \implode(PHP_EOL, $this->toArray());
    }
}
