<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class LintErrorsCollection extends ArrayCollection
{
    public function __toString(): string
    {
        $errors = [];
        foreach ($this->getIterator() as $error) {
            $errors[] = $error->__toString();
        }

        return implode(PHP_EOL, $errors);
    }
}
