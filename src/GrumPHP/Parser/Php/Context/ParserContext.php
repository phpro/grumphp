<?php

namespace GrumPHP\Parser\Php\Context;

use GrumPHP\Collection\ParseErrorsCollection;
use SplFileInfo;

class ParserContext
{
    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var ParseErrorsCollection
     */
    private $errors;

    /**
     * ParserContext constructor.
     *
     * @param SplFileInfo          $file
     * @param ParseErrorsCollection $errors
     */
    public function __construct(SplFileInfo $file, ParseErrorsCollection $errors)
    {
        $this->file = $file;
        $this->errors = $errors;
    }

    /**
     * @return SplFileInfo
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return ParseErrorsCollection
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
