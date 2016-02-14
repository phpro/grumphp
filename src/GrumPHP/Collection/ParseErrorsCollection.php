<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ParseErrorsCollection
 *
 * @package GrumPHP\Collection
 */
class ParseErrorsCollection extends ArrayCollection
{
    /**
     * @return string
     */
    public function __toString()
    {
        $errors = array();
        foreach ($this->getIterator() as $error) {
            $errors[] = $error->__toString();
        }

        return implode(PHP_EOL, $errors);
    }
}
