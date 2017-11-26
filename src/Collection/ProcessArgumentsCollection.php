<?php declare(strict_types=1);

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
    public static function forExecutable(string $executable): ProcessArgumentsCollection
    {
        return new ProcessArgumentsCollection([$executable]);
    }

    /**
     * @param string $argument
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
     * @param string $argument
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

    /**
     * @param string $argument
     * @param array  $values
     * @param string $delimiter
     */
    public function addOptionalCommaSeparatedArgument(string $argument, array $values, string $delimiter = ',')
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
    public function addArgumentArray(string $argument, array $values)
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
     * @param string $argument
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
     * @param string $argument
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
