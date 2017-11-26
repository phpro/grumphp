<?php declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Exception\InvalidArgumentException;

class ProcessArgumentsCollection extends ArrayCollection
{
    /**
     * @return ProcessArgumentsCollection
     */
    public static function forExecutable(string $executable): ProcessArgumentsCollection
    {
        return new ProcessArgumentsCollection([$executable]);
    }

    /**
     * @param string|bool|int $value
     */
    public function addOptionalArgument(string $argument, $value)
    {
        if (!$value) {
            return;
        }

        $this->add(sprintf($argument, $value));
    }

    /**
     * @param string|null $value
     */
    public function addOptionalArgumentWithSeparatedValue(string $argument, $value)
    {
        if (!$value) {
            return;
        }

        $this->add($argument);
        $this->add($value);
    }

    public function addOptionalCommaSeparatedArgument(string $argument, array $values, string $delimiter = ',')
    {
        if (!count($values)) {
            return;
        }

        $this->add(sprintf($argument, implode($delimiter, $values)));
    }

    public function addArgumentArray(string $argument, array $values)
    {
        foreach ($values as $value) {
            $this->add(sprintf($argument, $value));
        }
    }

    /**
     * Some CLI tools prefer to split the argument and the value.
     */
    public function addArgumentArrayWithSeparatedValue($argument, array $values)
    {
        foreach ($values as $value) {
            $this->add(sprintf($argument, $value));
            $this->add($value);
        }
    }

    public function addSeparatedArgumentArray(string $argument, array $values)
    {
        if (!count($values)) {
            return;
        }

        $this->add($argument);
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * @param string|int $value
     */
    public function addRequiredArgument(string $argument, $value)
    {
        if (!$value) {
            throw new InvalidArgumentException(sprintf('The argument %s is required.', $argument));
        }

        $this->add(sprintf($argument, $value));
    }

    /**
     * @param FilesCollection|\SplFileInfo[] $files
     */
    public function addFiles(FilesCollection $files)
    {
        foreach ($files as $file) {
            $this->add($file->getPathname());
        }
    }

    /**
     * @param FilesCollection|\SplFileInfo[] $files
     */
    public function addCommaSeparatedFiles(FilesCollection $files)
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        $this->add(implode(',', $paths));
    }

    /**
     * @param FilesCollection|\SplFileInfo[] $files
     */
    public function addArgumentWithCommaSeparatedFiles(string $argument, FilesCollection $files)
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        $this->add(sprintf($argument, implode(',', $paths)));
    }
}
