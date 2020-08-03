<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Exception\InvalidArgumentException;

/**
 * @extends ArrayCollection<int, string>
 */
class ProcessArgumentsCollection extends ArrayCollection
{
    public static function forExecutable(string $executable): self
    {
        return new self([$executable]);
    }

    /**
     * @param mixed $value
     */
    public function addOptionalArgument(string $argument, $value = null): void
    {
        if (!$value) {
            return;
        }

        $this->add(sprintf($argument, $value));
    }

    /**
     * @param string|null|int $value
     */
    public function addOptionalArgumentWithSeparatedValue(string $argument, $value = null): void
    {
        if (!$value) {
            return;
        }

        $this->add($argument);
        $this->add((string) $value);
    }

    public function addOptionalCommaSeparatedArgument(string $argument, array $values, string $delimiter = ','): void
    {
        if (!\count($values)) {
            return;
        }

        $this->add(sprintf($argument, implode($delimiter, $values)));
    }

    public function addArgumentArray(string $argument, array $values): void
    {
        foreach ($values as $value) {
            $this->add(sprintf($argument, $value));
        }
    }

    /**
     * Some CLI tools prefer to split the argument and the value.
     */
    public function addArgumentArrayWithSeparatedValue(string $argument, array $values): void
    {
        foreach ($values as $value) {
            $this->add(sprintf($argument, $value));
            $this->add($value);
        }
    }

    public function addSeparatedArgumentArray(string $argument, array $values): void
    {
        if (!\count($values)) {
            return;
        }

        $this->add($argument);
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function addRequiredArgument(string $argument, string $value): void
    {
        if (!$value) {
            throw new InvalidArgumentException(sprintf('The argument %s is required.', $argument));
        }

        $this->add(sprintf($argument, $value));
    }

    public function addFiles(FilesCollection $files): void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    public function addFile(\SplFileInfo $file): void
    {
        $this->add($file->getPathname());
    }

    public function addCommaSeparatedFiles(FilesCollection $files): void
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        $this->add(implode(',', $paths));
    }

    public function addArgumentWithCommaSeparatedFiles(string $argument, FilesCollection $files): void
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->getPathname();
        }

        $this->add(sprintf($argument, implode(',', $paths)));
    }

    public function addOptionalBooleanArgument(
        string $argument,
        ?bool $value,
        string $trueFormat,
        string $falseFormat
    ): void {
        if (null === $value) {
            return;
        }

        $this->add(sprintf($argument, $value ? $trueFormat : $falseFormat));
    }

    public function addOptionalIntegerArgument(string $argument, ?int $value): void
    {
        $this->addOptionalMixedArgument($argument, $value);
    }

    /**
     * @param string $argument
     * @param mixed $value
     */
    public function addOptionalMixedArgument(string $argument, $value): void
    {
        if (null === $value) {
            return;
        }

        $this->add(sprintf($argument, $value));
    }
}
