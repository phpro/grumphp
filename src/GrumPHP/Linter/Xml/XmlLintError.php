<?php

namespace GrumPHP\Linter\Xml;

use GrumPHP\Linter\LintError;
use LibXMLError;

/**
 * Class XmlLintError
 *
 * @package GrumPHP\Linter\Xml
 */
class XmlLintError extends LintError
{

    /**
     * @param LibXMLError $error
     *
     * @return XmlLintError
     */
    public static function fromLibXmlError(LibXMLError $error)
    {
        $type = LintError::TYPE_NONE;
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $type = LintError::TYPE_WARNING;
                break;
            case LIBXML_ERR_FATAL:
                $type = LintError::TYPE_FATAL;
                break;
            case LIBXML_ERR_ERROR:
                $type = LintError::TYPE_ERROR;
                break;
        }

        return new XmlLintError($type, $error->code, $error->message, $error->file, $error->line, $error->column);
    }
}
