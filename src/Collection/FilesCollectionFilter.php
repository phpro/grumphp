<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Closure;
use GrumPHP\Util\Regex;
use Symfony\Component\Finder\Comparator;
use Symfony\Component\Finder\Iterator;
use Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use Traversable;

/**
 * Stateless helper that applies SplFileInfo-based filters on a FilesCollection
 * of string paths. SplFileInfo instances are created transiently for the
 * Symfony Finder iterators
 */
final class FilesCollectionFilter
{
    /**
     * Adds a rule that files must match.
     *
     * You can use a pattern (delimited with / sign), a glob or a simple string.
     *
     * FilesCollectionFilter::name($collection, '*.php')
     * FilesCollectionFilter::name($collection, '/\.php$/') // same as above
     * FilesCollectionFilter::name($collection, 'test.php')
     *
     * @param string|Regex $pattern A pattern (a regexp, a glob, or a string)
     */
    public static function name(FilesCollection $files, $pattern): FilesCollection
    {
        return self::names($files, [$pattern]);
    }

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * FilesCollectionFilter::names($collection, ['*.php'])
     * FilesCollectionFilter::names($collection, ['/\.php$/']) // same as above
     * FilesCollectionFilter::names($collection, ['test.php'])
     */
    public static function names(FilesCollection $files, array $patterns): FilesCollection
    {
        $filter = new Iterator\FilenameFilterIterator(self::toFileInfoIterator($files), $patterns, []);

        return self::toFilesCollection($filter);
    }

    /**
     * Adds rules that files must not match.
     *
     * You can use a pattern (delimited with / sign), a glob or a simple string.
     *
     * FilesCollectionFilter::notName($collection, '*.php')
     * FilesCollectionFilter::notName($collection, '/\.php$/') // same as above
     * FilesCollectionFilter::notName($collection, 'test.php')
     */
    public static function notName(FilesCollection $files, string $pattern): FilesCollection
    {
        $filter = new Iterator\FilenameFilterIterator(self::toFileInfoIterator($files), [], [$pattern]);

        return self::toFilesCollection($filter);
    }

    /**
     * Filter by path.
     *
     * FilesCollectionFilter::path($collection, '/^spec\/')
     */
    public static function path(FilesCollection $files, string $pattern): FilesCollection
    {
        return self::paths($files, [$pattern]);
    }

    /**
     * Filter by paths.
     *
     * FilesCollectionFilter::paths($collection, ['/^spec\/','/^src\/'])
     */
    public static function paths(FilesCollection $files, array $patterns): FilesCollection
    {
        $filter = new Iterator\PathFilterIterator(self::toFileInfoIterator($files), $patterns, []);

        return self::toFilesCollection($filter);
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * FilesCollectionFilter::notPath($collection, '/^spec\/')
     */
    public static function notPath(FilesCollection $files, string $pattern): FilesCollection
    {
        return self::notPaths($files, [$pattern]);
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * FilesCollectionFilter::notPaths($collection, ['/^spec\/','/^src\/'])
     */
    public static function notPaths(FilesCollection $files, array $patterns): FilesCollection
    {
        $filter = new Iterator\PathFilterIterator(self::toFileInfoIterator($files), [], $patterns);

        return self::toFilesCollection($filter);
    }

    public static function extensions(FilesCollection $files, array $extensions): FilesCollection
    {
        if (!\count($extensions)) {
            return new FilesCollection();
        }

        return self::name($files, sprintf('/\.(%s)$/i', implode('|', $extensions)));
    }

    /**
     * Adds tests for file sizes.
     *
     * FilesCollectionFilter::size($collection, '> 10K');
     * FilesCollectionFilter::size($collection, '<= 1Ki');
     * FilesCollectionFilter::size($collection, '4');
     *
     * @see NumberComparator
     */
    public static function size(FilesCollection $files, string $size): FilesCollection
    {
        $comparator = new Comparator\NumberComparator($size);
        $filter = new Iterator\SizeRangeFilterIterator(self::toFileInfoIterator($files), [$comparator]);

        return self::toFilesCollection($filter);
    }

    /**
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     * FilesCollectionFilter::date($collection, 'since yesterday');
     * FilesCollectionFilter::date($collection, 'until 2 days ago');
     * FilesCollectionFilter::date($collection, '> now - 2 hours');
     * FilesCollectionFilter::date($collection, '>= 2005-10-15');
     *
     * @see DateComparator
     */
    public static function date(FilesCollection $files, string $date): FilesCollection
    {
        $comparator = new Comparator\DateComparator($date);
        $filter = new Iterator\DateRangeFilterIterator(self::toFileInfoIterator($files), [$comparator]);

        return self::toFilesCollection($filter);
    }

    /**
     * Filters the iterator with an anonymous function.
     *
     * @see CustomFilterIterator
     */
    public static function filter(FilesCollection $files, Closure $p): FilesCollection
    {
        $filter = new Iterator\CustomFilterIterator(self::toFileInfoIterator($files), [$p]);

        return self::toFilesCollection($filter);
    }

    public static function ignoreSymlinks(FilesCollection $files): FilesCollection
    {
        return self::filter($files, static function (\SplFileInfo $file): bool {
            return !$file->isLink();
        });
    }

    /**
     * Keeps only files whose pathname is present in the given file list.
     *
     * @param Traversable<array-key, \SplFileInfo> $fileList
     */
    public static function filterByFileList(FilesCollection $files, Traversable $fileList): FilesCollection
    {
        $allowedFiles = [];
        foreach ($fileList as $file) {
            $allowedFiles[$file->getPathname()] = true;
        }

        return self::filter($files, static function (\SplFileInfo $file) use ($allowedFiles): bool {
            return isset($allowedFiles[$file->getPathname()]);
        });
    }

    /**
     * @return \Generator<string, SymfonySplFileInfo>
     */
    private static function toFileInfoIterator(FilesCollection $files): \Generator
    {
        foreach ($files as $path) {
            yield $path => new SymfonySplFileInfo($path, \dirname($path), $path);
        }
    }

    /**
     * @param Traversable<array-key, \SplFileInfo> $iterator
     */
    private static function toFilesCollection(Traversable $iterator): FilesCollection
    {
        $paths = [];
        foreach ($iterator as $file) {
            $paths[] = $file->getPathname();
        }

        return new FilesCollection($paths);
    }
}
