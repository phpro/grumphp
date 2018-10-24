<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Exception\InvalidArgumentException;

class ProcessArgumentsCollection extends ArrayCollection
{
    public static function forExecutable(string $executable): self
    {
        return new self([$executable]);
    }

    /**
     * @param string|null $value
     */
    public function addOptionalArgument(string $argument, $value)
    {
        if (!$value) {
            return;
        }

        $this->add(sprintf($argument, $value));
    }

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
        if (!\count($values)) {
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
    public function addArgumentArrayWithSeparatedValue(string $argument, array $values)
    {
        foreach ($values as $value) {
            $this->add(sprintf($argument, $value));
            $this->add($value);
        }
    }

    public function addSeparatedArgumentArray(string $argument, array $values)
    {
        if (!\count($values)) {
            return;
        }

        $this->add($argument);
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function addRequiredArgument(string $argument, string $value)
    {
        if (!$value) {
            throw new InvalidArgumentException(sprintf('The argument %s is required.', $argument));
        }

        $this->add(sprintf($argument, $value));
    }

    public function addFiles(FilesCollection $files)
    {
        foreach ($files as $file) {
            $this->add($file->getPathname());
        }
    }

    public function addCommaSeparatedFiles(FilesCollection $files)
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        $this->add(implode(',', $paths));
    }

    public function addArgumentWithCommaSeparatedFiles(string $argument, FilesCollection $files)
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        $this->add(sprintf($argument, implode(',', $paths)));
    }

    public function addOptionalBooleanArgument(string $argument, $value, string $trueFormat, string $falseFormat)
    {
        if (null === $value) {
            return;
        }

        $this->add(sprintf($argument, $value ? $trueFormat : $falseFormat));
    }

    /**
     * @param string   $argument
     * @param int|null $value
     */
    public function addOptionalIntegerArgument(string $argument, $value)
    {
        if (null === $value) {
            return;
        }

        $this->add(sprintf($argument, $value));
    }
}
