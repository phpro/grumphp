<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Exception\InvalidArgumentException;

class ProcessArgumentsCollection extends ArrayCollection
{
    /**
     * @param string $executable
     *
     * @return ProcessArgumentsCollection
     */
    public static function forExecutable($executable)
    {
        return new ProcessArgumentsCollection([$executable]);
    }

    /**
     * @param string $argument
     * @param string $value
     */
    public function addOptionalArgument($argument, $value)
    {
        if (!$value) {
            return;
        }

        $this->add(sprintf($argument, $value));
    }

    /**
     * @param string $argument
     * @param string $value
     */
    public function addOptionalArgumentWithSeparatedValue($argument, $value)
    {
        if (!$value) {
            return;
        }

        $this->add($argument);
        $this->add($value);
    }

    /**
     * @param string $argument
     * @param array  $values
     * @param string $delimiter
     */
    public function addOptionalCommaSeparatedArgument($argument, array $values, $delimiter = ',')
    {
        if (!count($values)) {
            return;
        }

        $this->add(sprintf($argument, implode($delimiter, $values)));
    }

    /**
     * @param string $argument
     * @param array  $values
     */
    public function addArgumentArray($argument, array $values)
    {
        foreach ($values as $value) {
            $this->add(sprintf($argument, $value));
        }
    }

    /**
     * Some CLI tools prefer to split the argument and the value.
     *
     * @param       $argument
     * @param array $values
     */
    public function addArgumentArrayWithSeparatedValue($argument, array $values)
    {
        foreach ($values as $value) {
            $this->add(sprintf($argument, $value));
            $this->add($value);
        }
    }

    /**
     * @param string $argument
     * @param array  $values
     */
    public function addSeparatedArgumentArray($argument, array $values)
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
     * @param string $argument
     * @param string $value
     */
    public function addRequiredArgument($argument, $value)
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
     * @param string $argument
     * @param FilesCollection|\SplFileInfo[] $files
     */
    public function addArgumentWithCommaSeparatedFiles($argument, FilesCollection $files)
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        $this->add(sprintf($argument, implode(',', $paths)));
    }
}
