<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class LintErrorsCollection
 *
 * @package GrumPHP\Collection
 */
class LintErrorsCollection extends ArrayCollection
{
    /**
     * @return string
     */
    public function __toString()
    {
        $errors = [];
        foreach ($this->getIterator() as $error) {
            $errors[] = $error->__toString();
        }

        return implode(PHP_EOL, $errors);
    }
}
